<template>
  <label class="block">
    <span v-if="label" class="wz-label">{{ label }}</span>
    <span class="relative block" :class="width">
      <input :value="modelValue ?? ''" @input="onInput" type="text" inputmode="numeric" :placeholder="placeholder"
        class="wz-input" :class="[{ 'wz-input--bad': invalid }, suffix ? '!pr-[64px]' : '']" />
      <span v-if="suffix" class="absolute right-[14px] top-1/2 -translate-y-1/2 text-[14px] text-[#94a3b8] pointer-events-none">{{ suffix }}</span>
    </span>
    <span v-if="invalid && error" class="wz-err block">{{ error }}</span>
    <span v-else-if="hint" class="wz-hint block">{{ hint }}</span>
  </label>
</template>

<script setup>
// Число без «спиннеров» браузера: только цифры, хранится как число (как раньше в черновике), пустое — null
const props = defineProps({
  modelValue: [Number, String],
  label: String,
  suffix: String,
  hint: String,
  error: String,
  invalid: Boolean,
  placeholder: String,
  max: { type: Number, default: 9 }, // максимум цифр
  width: { type: String, default: 'max-w-[220px]' },
})
const emit = defineEmits(['update:modelValue'])

function onInput(e) {
  const digits = e.target.value.replace(/\D/g, '').slice(0, props.max)
  e.target.value = digits
  emit('update:modelValue', digits === '' ? null : parseInt(digits, 10))
}
</script>
