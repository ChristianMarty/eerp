<template>
  <div class="app-container">
    <h1>
      WO-{{ workOrderData.WorkOrderNo }} --- {{ workOrderData.Titel }} ---
      {{ workOrderData.ProjectTitel }}
    </h1>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'WorkOrderView',
  components: {},
  data() {
    return {
      workOrderData: null
    }
  },
  mounted() {
    this.getWorkOrderData()
    // this.setTagsViewTitle();
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    getWorkOrderData() {
      requestBN({
        url: '/workOrder/item',
        methood: 'get',
        params: { WorkOrderNo: this.$route.params.workOrderNo }
      }).then(response => {
        this.workOrderData = response.data
      })
    },
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.projectNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    }
  }
}
</script>
