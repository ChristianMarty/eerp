<template>
  <div class="partCharacteristics-dialog">
    <el-dialog
      title="Part Characteristics Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <h2>Class:</h2>
      <el-cascader-panel
        v-model="data.PartClassId"
        :options="classes"
        :props="{
          emitPath: false,
          value: 'Id',
          label: 'Name',
          children: 'Children',
          checkStrictly: true
        }"
      />



      <!--
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
      <el-divider />-->
      <span>
        <el-button type="primary" @click="saveData()">Save</el-button>
        <el-button @click="closeDialog()">Cancel</el-button>
      </span>

    </el-dialog>
  </div>
</template>

<script>

import Part from '@/api/part'
const part = new Part()

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'PartCharacteristicsDialog',
  props: {
    partId: { type: Number, default: 0 },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      classes: [],
      classId: null,
      data: {}
    }
  },
  mounted() {
    this.getClasses()
  },
  methods: {
    async onOpen() {
      this.getData()
    },
    getClasses() {
      part.class.list(0).then(response => {
        this.classes = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    getData() {
      manufacturerPart.item.characteristics.get(this.$props.partId).then(response => {
        this.data = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    saveData() {
      manufacturerPart.item.characteristics.save(this.$props.partId, this.data.PartClassId).then(response => {
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    handleClose(tag) {

    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>
