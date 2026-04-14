<template>
  <div v-if="publisherId && slot" class="ad-unit my-4" aria-label="Annuncio pubblicitario">
    <ins
      class="adsbygoogle"
      style="display:block"
      :data-ad-client="publisherId"
      :data-ad-slot="slot"
      data-ad-format="auto"
      data-full-width-responsive="true"
    ></ins>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const props = defineProps({
  publisherId: { type: String, required: true },
  slot:        { type: String, required: true },
});

onMounted(() => {
  if (!props.publisherId || !props.slot) return;
  try {
    // eslint-disable-next-line no-undef
    (window.adsbygoogle = window.adsbygoogle || []).push({});
  } catch {
    // AdSense non ancora caricato o bloccato da ad-blocker
  }
});
</script>
