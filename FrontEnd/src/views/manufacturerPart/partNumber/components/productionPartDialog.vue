<template>
  <div class="productionPart-dialog">
    <el-dialog
      title="Production Part Number Mapping"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-input ref="prodPartSearchInput" v-model="prodPartSearchInput" placeholder="Production Part Number" @keyup.enter.native="searchProductionPart()">
        <el-button slot="append" icon="el-icon-plus" @click="searchProductionPart()" />
      </el-input>
      <p />
      <el-divider />
      <p><b>Selected:</b></p>
      <span>
        <el-popover
          v-for="tag in productionPartList"
          :key="tag.ItemCode"
          placement="top-start"
          width="200"
          trigger="hover"
        >
          <p><b>{{ tag.ItemCode }}</b></p>
          <p>{{ tag.Description }}</p>

          <el-tag
            slot="reference"
            style="margin: 5px"
            closable
            :disable-transitions="false"
            @close="handleClose(tag)"
          >
            {{ tag.ItemCode }}
          </el-tag>
        </el-popover>
      </span>
      <el-divider />
      <span>
        <el-button type="primary" @click="save()">Save</el-button>
        <el-button @click="closeDialog()">Cancel</el-button>
      </span>

    </el-dialog>
  </div>
</template>

<script>

import ProductionPart from '@/api/productionPart'
const productionPart = new ProductionPart()

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'ProductionPartMapping',
  props: {
    manufacturerPartId: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      productionPartList: [],
      prodPartSearchInput: ''
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      this.getProductionPart()
    },
    getProductionPart() {
      productionPart.search(null, this.$props.manufacturerPartId).then(response => {
        this.productionPartList = response
      })
    },
    searchProductionPart() {
      productionPart.search(this.prodPartSearchInput).then(response => {
        if (response.length === 0) {
          this.$message({
            showClose: true,
            message: 'Production Part Number not found',
            duration: 2000,
            type: 'warning'
          })
        } else {
          this.productionPartList.push(response[0])
        }
      })
    },
    handleClose(tag) {
      this.productionPartList.splice(this.productionPartList.indexOf(tag), 1)
    },
    save() {
      var temp = this.productionPartList.map(item => item.ItemCode)
      manufacturerPart.PartNumber.productionPart.saveMapping(this.$props.manufacturerPartId, temp).then(response => {
        this.closeDialog()
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
