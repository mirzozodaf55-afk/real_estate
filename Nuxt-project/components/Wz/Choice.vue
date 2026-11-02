<template>
  <div>
    <span v-if="label" class="wz-label">{{ label }}</span>
    <div class="flex flex-wrap gap-[8px]" role="group" :aria-label="label">
      <button v-for="o of normalized" :key="String(o.value)" type="button" @click="toggle(o.value)"
        :aria-pressed="isOn(o.value)"
        class="min-h-[42px] px-[14px] rounded-[11px] border-[1.5px] text-[14.5px] font-semibold transition-colors flex items-center gap-[8px]"
        :class="isOn(o.value)
          ? 'border-[#0468ff] bg-[#eef4ff] text-[#0468ff]'
          : invalid ? 'border-[#fecaca] bg-white text-[#334155] hover:border-[#f87171]' : 'border-[#e2e8f0] bg-white text-[#334155] hover:border-[#94a3b8]'">
        <font-awesome-icon v-if="o.icon" :icon="o.icon" class="text-[14px]" />
        {{ o.label }}
        <font-awesome-icon v-if="multiple && isOn(o.value)" :icon="['fas', 'check']" class="text-[12px]" />
      </button>
    </div>
    <p v-if="invalid && error" class="wz-err">{{ error }}</p>
    <p v-else-if="hint" class="wz-hint">{{ hint }}</p>
  </div>
</template>

<script setup>
// Выбор «плашками» вместо выпадающих списков и радиокнопок: всё видно сразу и легко нажать на телефоне
const props = defineProps({
  modelValue: [String, Number, Array, Boolean],
  options: { type: Array, required: true }, // строки или { value, label, icon }
  label: String,
  hint: String,
  error: String,
  invalid: Boolean,
  multiple: Boolean,
})
const emit = defineEmits(['update:modelValue'])

const normalized = computed(() => props.options.map((o) => (typeof o === 'object' ? o : { value: o, label: String(o) })))
const isOn = (v) => (props.multiple ? (props.modelValue || []).includes(v) : props.modelValue === v)

function toggle(v) {
  if (!props.multiple) return emit('update:modelValue', v)
  const list = [...(props.modelValue || [])]
  const i = list.indexOf(v)
  i === -1 ? list.push(v) : list.splice(i, 1)
  emit('update:modelValue', list)
}
</script>
