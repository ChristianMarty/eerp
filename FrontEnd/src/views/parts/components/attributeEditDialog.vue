<template>
  <div class="attribute-edit-dialog">
    <el-dialog
      title="Attribute Edit"
      width="80%"
      :visible.sync="visible"
      :before-close="closeDialog"
      @open="init"
    >
      <!-- <el-cascader-panel
        v-model="partClass"
        :options="partClasses"
        :props="{
          emitPath: false,
          value: 'Id',
          label: 'Name',
          children: 'Children',
          disabled: 'NoParts',
          checkStrictly: true
        }"
      />-->

      <el-table :data="attributeData">
        <el-table-column label="Name" prop="Name" />

        <el-table-column label="Min">
          <template v-if="row.Value.Minimum != null" slot-scope="{ row }">
            <el-input v-model="row.Value.Minimum" />
          </template>
        </el-table-column>

        <el-table-column label="Typ">
          <template slot-scope="{ row }">
            <el-input v-model="row.Value.Typical" />
          </template>
        </el-table-column>

        <el-table-column label="Max">
          <template v-if="row.Value.Maximum != null" slot-scope="{ row }">
            <el-input v-model="row.Value.Maximum" />
          </template>
        </el-table-column>

        <el-table-column label="Unit" prop="Unit" />
      </el-table>
      <span slot="footer" class="dialog-footer">
        <el-button @click="closeDialog">Close</el-button>
        <el-button type="primary" @click="savePartData">Save</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  props: {
    partId: { type: Number, default: null },
    visible: { type: Boolean, default: true }
  },
  data() {
    return {
      partClasses: null,
      partClass: null,
      partData: null,
      attributeData: []
    }
  },
  mounted() {
    this.init()
  },
  methods: {
    init() {
      this.getPartClasses()
      this.getPartData()
    },
    getAttributes(classId) {
      requestBN({
        url: '/part/attribute/',
        methood: 'get',
        params: { classId: classId }
      }).then(response => {
        this.attributes = response.data
      })
    },
    getPartClasses() {
      requestBN({
        url: '/part/class/',
        methood: 'get'
      }).then(response => {
        this.partClasses = response.data
      })
    },
    getPartData() {
      requestBN({
        url: '/part/item',
        methood: 'get',
        params: { PartId: this.$route.params.partId }
      }).then(response => {
        this.partData = response.data[0]
        this.attributeData = []

        this.partData.PartData.forEach(element => {
          const valArr = { Minimum: null, Maximum: null, Typical: null }

          if (typeof element.Value !== 'object') {
            valArr.Typical = element.Value
            element.Value = valArr
          }

          this.attributeData.push(element)
        })
      })
    },
    savePartData() {
      requestBN({
        method: 'patch',
        url: '/part/item',
        params: { PartId: this.inputItemNr },
        data: { data: this.partData }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else {
          this.$message({
            message: 'Quantity updated successfully',
            type: 'success'
          })

          this.closeDialog()
        }
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    save() {}
  }
}
</script>
