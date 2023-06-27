<template>
  <div class="app-container">
    <h2> {{ data.ManufacturerName }} - {{ data.PartNumber }}</h2>
    <p><b>Part:</b>
      <router-link
        :to="'/manufacturerPart/item/' + data.PartId"
        class="link-type"
      >
        <span>{{ data.NumberTemplate }}</span>
      </router-link>
    </p>
    <p><b>Series:</b>
      <router-link :to="'/manufacturerPart/series/item/' + data.SeriesId" class="link-type">
        <span> {{ data.SeriesTitle }}</span> - {{ data.SeriesDescription }}
      </router-link>
    </p>
    <p><b>Package: </b>{{ data.PackageName }}</p>

  </div>
</template>

<script>
import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'PartSeriesItem',

  data() {
    return {
      loading: true,

      data: {}
    }
  },
  mounted() {
    this.getManufacturerPartNumberItem()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: this.data.ManufacturerName + ' - ' + this.data.PartNumber
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = this.data.ManufacturerName + ' - ' + this.data.PartNumber
    },
    getManufacturerPartNumberItem() {
      manufacturerPart.PartNumber.get(this.$route.params.ManufacturerPartNumberId).then(response => {
        this.data = response
        this.setTitle()

        this.loading = false
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
