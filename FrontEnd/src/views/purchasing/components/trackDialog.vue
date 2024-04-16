<template>
  <div class="track-dialog">
    <el-dialog
      title="Track Item"
      :visible.sync="visible"
      width="50%"
      center
      :before-close="closeDialog"
      @open="loadData()"
    >
      <trackTable :data="trackData" />
    </el-dialog>
  </div>
</template>

<script>

import trackTable from './trackTable.vue'

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'TrackDialog',
  components: { trackTable },
  props: { receivalId: { type: Number, default: 0 }, visible: { type: Boolean, default: false }},
  data() {
    return {
      trackData: null
    }
  },
  mounted() {
  },
  methods: {
    loadData() {
      this.getTrackData()
    },
    getTrackData() {
      purchase.item.track(this.$props.receivalId).then(response => {
        this.trackData = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>
