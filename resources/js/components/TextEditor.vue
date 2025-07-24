<template>
    <div class="space-y-4">
        <div class="flex gap-2">
            <button type="button" @click.prevent="wrapSelection('b')" class="px-2 py-1 rounded bg-blue-500 text-white hover:bg-blue-600">
                Bold
            </button>
            <button type="button" @click.prevent="wrapSelection('i')" class="px-2 py-1 rounded bg-green-500 text-white hover:bg-green-600">
                Italic
            </button>
            <button type="button" @click.prevent="wrapSelection('u')" class="px-2 py-1 rounded bg-purple-500 text-white hover:bg-purple-600">
                Underline
            </button>
        </div>

        <div
            ref="editor"
            contenteditable
            class="border border-gray-300 rounded p-4 min-h-[150px] whitespace-pre-wrap"
            @input="onInput"
        ></div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
    modelValue: String
})
const emit = defineEmits(['update:modelValue'])

const editor = ref(null)
const tagMap = { b: 'strong', i: 'em', u: 'u' }
const rawText = ref(props.modelValue || '')
let lastCaretOffset = null

function parseSimpleTags(raw) {
    return raw.replace(/\{\{(b|i|u)\}\}([\s\S]*?)\{\{\/\1\}\}/g, (_, tag, content) => {
        const htmlTag = tagMap[tag]
        return `<${htmlTag}>${content}</${htmlTag}>`
    })
}

function reverseSimpleTags(html) {
    let raw = html
    for (const [tag, htmlTag] of Object.entries(tagMap)) {
        const regex = new RegExp(`<${htmlTag}>([\s\S]*?)<\/${htmlTag}>`, 'gi')
        raw = raw.replace(regex, `{{${tag}}}$1{{/${tag}}}`)
    }
    return raw
}

onMounted(() => {
    updateHtmlContent(rawText.value)
})

watch(() => props.modelValue, (newVal) => {
    rawText.value = newVal
    updateHtmlContent(newVal)
})

function updateHtmlContent(raw) {
    const html = parseSimpleTags(raw)
    nextTick(() => {
        const el = editor.value
        if (!el) return
        const caretOffset = lastCaretOffset ?? getCaretCharacterOffsetWithin(el)
        el.innerHTML = html
        setCaretPosition(el, caretOffset)
    })
}

function onInput() {
    const el = editor.value
    if (!el) return
    lastCaretOffset = getCaretCharacterOffsetWithin(el)
    const html = el.innerHTML
    const raw = reverseSimpleTags(html)
    rawText.value = raw
    emit('update:modelValue', raw)
}

function getCaretCharacterOffsetWithin(element) {
    const sel = window.getSelection()
    if (!sel || sel.rangeCount === 0) return 0
    const range = sel.getRangeAt(0)
    const preCaretRange = range.cloneRange()
    preCaretRange.selectNodeContents(element)
    preCaretRange.setEnd(range.endContainer, range.endOffset)
    return preCaretRange.toString().length
}

function setCaretPosition(el, offset) {
    const range = document.createRange()
    const sel = window.getSelection()
    let currentOffset = 0

    function setOffset(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            const textLength = node.textContent.length
            if (currentOffset + textLength >= offset) {
                range.setStart(node, offset - currentOffset)
                range.collapse(true)
                sel.removeAllRanges()
                sel.addRange(range)
                return true
            }
            currentOffset += textLength
        } else {
            for (let i = 0; i < node.childNodes.length; i++) {
                if (setOffset(node.childNodes[i])) return true
            }
        }
        return false
    }

    setOffset(el)
}

function wrapSelection(tag) {
    const sel = window.getSelection()
    if (!sel || sel.rangeCount === 0) return

    const range = sel.getRangeAt(0)
    const selectedText = range.toString()
    const caretOffset = getCaretCharacterOffsetWithin(editor.value)

    if (selectedText) {
        const startOffset = caretOffset - selectedText.length
        const endOffset = caretOffset
        const before = rawText.value.slice(0, startOffset)
        const after = rawText.value.slice(endOffset)
        rawText.value = `${before}{{${tag}}}${selectedText}{{/${tag}}}${after}`
        emit('update:modelValue', rawText.value)
        lastCaretOffset = startOffset + `{{${tag}}}`.length + selectedText.length
    } else {
        const before = rawText.value.slice(0, caretOffset)
        const after = rawText.value.slice(caretOffset)
        const placeholder = 'text'
        rawText.value = `${before}{{${tag}}}${placeholder}{{/${tag}}}${after}`
        emit('update:modelValue', rawText.value)
        lastCaretOffset = caretOffset + `{{${tag}}}`.length
    }

    updateHtmlContent(rawText.value)
}
</script>
