<template>
  <div class="part-series-template-edit-dialog">
    <el-dialog
      title="Template Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="80%"
      @opened="onOpen()"
    >
      <el-form label-width="170px">
        <el-form-item label="Name Match:">
          <el-input v-model="data.SeriesNameMatch" />
        </el-form-item>

        <el-form-item label="Number Template:">
          <el-input v-model="data.NumberTemplate" />
        </el-form-item>
        <el-form-item label="Number Parameter:">
          <div class="editor-container">
            <json-editor v-model="data.Parameter" />
          </div>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="save()">Save</el-button>
          <el-button @click="closeDialog()">Cancel</el-button>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script>
import JsonEditor from '@/components/JsonEditor'

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'PartSeriesTemplateEditDialog',
  components: { JsonEditor },
  props: {
    visible: { type: Boolean, default: false },
    manufacturerPartSeriesId: { type: Number, default: null }
  },
  data() {
    return {
      data: Object.assign({}, manufacturerPart.series.template.seriesTemplateParameters)
    }
  },
  mounted() {
  },
  methods: {
    onOpen() {
      this.getTemplateData()
    },
    getTemplateData() {
      if (this.$props.manufacturerPartSeriesId === null) return

      manufacturerPart.series.template.get(this.$props.manufacturerPartSeriesId).then(response => {
        this.data.ManufacturerPartSeriesId = response.ManufacturerPartSeriesId
        this.data.SeriesNameMatch = response.SeriesNameMatch
        this.data.NumberTemplate = response.NumberTemplate
        this.data.Parameter = response.Parameter
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 1500,
          type: 'error'
        })
      })
    },
    save() {
      this.data.Parameter = JSON.parse(this.data.Parameter)
      manufacturerPart.series.template.save(this.data).then(response => {
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 1500,
          type: 'error'
        })
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>
<style scoped>
.editor-container{
  position: relative;
  height: 100%;
}
</style>
