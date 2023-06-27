<template>
  <div class="app-container">
    <baseClassSearch v-if="$route.query.ClassId === undefined" :data="manufacturerPartClass" @select="onClassSelect" />
    <inClassSearch v-if="$route.query.ClassId !== undefined" :data="manufacturerPartClass" @select="onClassSelect" />
  </div>
</template>

<script>

import baseClassSearch from './searchComponents/baseClassSelect'
import inClassSearch from './searchComponents/inClassSearch'

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'ManufacturerPartSearch',
  components: { baseClassSearch, inClassSearch },
  data() {
    return {
      manufacturerPartClass: null // Object.assign({}, manufacturerPart.series.seriesCreateParameters)
    }
  },
  watch: {
    '$route.query': {
      handler(newVal) {
        this.getManufacturerPartClass(this.$route.query.ClassId)
      }
    }
  },
  mounted() {
    this.getManufacturerPartClass(0)
  },
  methods: {
    onClassSelect(ClassId) {
      if (ClassId === 0) this.$router.push({ query: { }})
      else this.$router.push({ query: { ClassId: ClassId }})
    },
    getManufacturerPartClass(ClassId) {
      manufacturerPart.class.search(ClassId).then(response => {
        this.manufacturerPartClass = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>

<style>
  .small-box {
    float: left;
    width: 330px;
    height: 330px;
    margin: 10px;
    cursor: pointer;
  }
  .headerClass {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
  }
  .clearfix:before,
  .clearfix:after {
    display: table;
    content: "";
    text-align: center;
  }
  .clearfix:after {
    clear: both
  }
</style>
