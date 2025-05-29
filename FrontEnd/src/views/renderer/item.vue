<template>
  <div class="app-container">
    <h2>{{ labelData.Name }}</h2>
    <el-divider />
    <p>{{ labelData.Description }}</p>
    <el-table :data="variables" border style="width: 100%">
      <el-table-column prop="Name" label="Variable" width="220" />
      <el-table-column prop="Value" label="Value">
        <template slot-scope="{ row }">
          <el-input v-model="row.Value" placeholder="Please input" @input="updateCode" />
        </template>
      </el-table-column>
    </el-table>

    <el-select v-model="selectedPrinterId">
      <el-option
        v-for="item in printer"
        :key="Number(item.Id)"
        :label="item.Name"
        :value="Number(item.Id)"
      />
    </el-select>
    <el-button
      type="primary"
      plain
      icon="el-icon-printer"
      style="margin-left: 20px"
      @click="print()"
    >Print</el-button>

    <el-collapse>
      <el-collapse-item title="Code">
        <template slot="title">
          <h3>Code</h3>
        </template>
        <pre>{{ code }}</pre>
      </el-collapse-item>
    </el-collapse>

    <h3>Preview</h3>
    <p>Rendered by <a href="http://labelary.com">labelary.com</a></p>
    <div>
      <img :src="previewPath" :style="previewStyle">
    </div>
  </div>
</template>

<script>
import Renderer from '@/api/renderer'
const renderer = new Renderer()

import Printer from '@/api/print'
const printer = new Printer()

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

export default {
  name: 'LabelItem',
  data() {
    return {
      labelId: 0,
      labelData: null,
      variables: [],
      code: '',
      previewPath: '',
      previewHeight: '0mm',
      previewWidth: '0mm',
      previewStyle: '',
      rotation: '90',
      printer: [],
      selectedPrinterId: 1
    }
  },
  mounted() {
    if (this.$route.params.Id != null) {
      this.labelId = this.$route.params.Id
      this.getLabel()
    }
    this.getPrinter()
  },
  methods: {
    prepairData() {
      this.labelData.Dataset.Variables.forEach(element => {
        const line = { Name: element, Value: '' }
        this.variables.push(line)
      })

      this.previewHeight = this.labelData.Medium.Hight * 2 + 'mm'
      this.previewWidth = this.labelData.Medium.Width * 2 + 'mm'
      this.rotation = this.labelData.Medium.Rotation
      this.updateCode()
    },
    getLabel() {
      renderer.item(this.labelId).then(response => {
        this.labelData = response
        this.prepairData()
      })
    },
    getPrinter() {
      peripheral.list(peripheral.Type.Printer).then(response => {
        this.printer = response
      })
    },
    updateCode() {
      this.code = JSON.parse(JSON.stringify(this.labelData.Code))
      this.variables.forEach(element => {
        this.code = this.code.replaceAll(element.Name, element.Value)
      })
      this.updateImage()
    },
    updateImage() {
      if (this.labelData.Language !== 'ZPL') return

      this.previewPath = 'https://api.labelary.com/v1/printers/'
      this.previewPath += this.labelData.Medium.Resolution
      this.previewPath += '/labels/'
      this.previewPath += parseInt(this.labelData.Medium.Width) / 25.4
      this.previewPath += 'x'
      this.previewPath += parseInt(this.labelData.Medium.Hight) / 25.4
      this.previewPath += '/0/'
      this.previewPath += this.code

      this.previewStyle = 'border: 1px solid; '
      if (this.rotation === 90) {
        this.previewStyle += 'transform: rotate(' + this.rotation + 'deg) '
        this.previewStyle += 'translateX(-100%) translateY(-33%);'
      }
    },
    print() {
      printer.print('raw', this.labelData.Language, this.selectedPrinterId, this.code).then(response => {
        this.printer = response
      })
    }
  }
}
</script>
