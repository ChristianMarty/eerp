<template>
  <div class="app-container">
    <baseClassSearch v-if="$route.query.ClassId === undefined" />
    <inClassSearch v-if="$route.query.ClassId !== undefined" />
  </div>
</template>

<script>

import baseClassSearch from './searchComponents/baseClassSelect'
import inClassSearch from './searchComponents/inClassSearch'

export default {
  name: 'ManufacturerPartSearch',
  components: { baseClassSearch, inClassSearch },
  data() {
    return {
    }
  },
  mounted() {
  },
  methods: {
  }
}
</script>
