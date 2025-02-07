<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { Job } from '../types'
import type { Line0 } from './types'
import ConsoleArg from './ConsoleArg.vue'
import TheIcon from '@common/components/TheIcon.vue'
import { mdiAlert, mdiCloseCircle } from '@mdi/js'

declare global {
  interface Window {
    console: Console;
  }
}

const props = defineProps<{
  job: Job,
  seq: number
}>()
const { job, seq } = props
const iframe = ref<HTMLIFrameElement | null>(null)
const lines = ref<Line0[]>([])

onMounted(() => {
  if (!iframe.value) return
  const { contentDocument: doc, contentWindow: win } = iframe.value
  if (!doc || !win) return
  win.console = new Proxy(console, {
    get(_, prop) {
      return (...args: unknown[]) => {
        const sev = prop as string
        lines.value.push({ sev, args })
      }
    }
  })
  doc.open()
  doc.write(job.boxes.map((b) => (b.lang === 'javascript' ? `<script>${b.text}<\/script>` : b.text)).join(''))
  doc.close()
})
</script>

<template>
  <div class="p-4 border rounded bg-zinc-50 dark:bg-zinc-950">
    <slot />
  </div>
  <div v-if='seq === job.main' class="border">
    <iframe ref="iframe" class="w-full border bg-white" :class="{ hidden: !job.boxes.some(b => b.lang === 'html') }"
      title="" />
    <div v-if="lines.length > 0" class="border font-mono text-sm p-2 pb-5">
      <div v-for="(line, i) in lines" :key="i" class="line" :class="line.sev">
        <span class="text-center">
          <template v-if="line.sev == 'warn'">
            <TheIcon :path="mdiAlert" :size="13" />
          </template>
          <template v-else-if="line.sev == 'error'">
            <TheIcon :path="mdiCloseCircle" :size="13" />
          </template>
        </span>
        <span>
          <span v-for="(arg, i) in line.args" :key="i">
            <span v-if="i != 0">&nbsp;</span>
            <ConsoleArg :arg="arg" :depth="0" :expand="false" :summary="false" />
          </span>
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.line {
  @apply m-0.5 grid grid-cols-[30px_1fr] rounded;
}

.warn {
  @apply bg-yellow-400 bg-opacity-15 text-orange-400;
}

.error {
  @apply bg-red-400 bg-opacity-15 text-red-400;
}

.log+.log .col {
  @apply border-t pt-0.5;
}
</style>
