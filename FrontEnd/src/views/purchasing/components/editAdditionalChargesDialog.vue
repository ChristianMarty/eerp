<template>
  <div class="edit-additional-charges-dialog">
    <el-dialog title="Additional Charge" :visible.sync="visible">

      <el-form label-width="150px">
        <el-form-item label="Line:">{{ line.LineNo }}</el-form-item>
        <el-form-item label="Type:">
          <el-select
            v-model="line.Type"
            placeholder="Type"
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option v-for="item in additionalChargeTypes" :key="item" :label="item" :value="item" />
          </el-select>
        </el-form-item>

        <el-form-item label="Quantity:">
          <template slot-scope="{ row }">
            <el-input-number
              v-model="line.Quantity"
              :controls="false"
              :min="1"
              :max="999999"
              style="width: 70pt"
            />
          </template>
        </el-form-item>

        <el-form-item label="Description:">
          <el-input v-model="line.Description" />
        </el-form-item>

        <el-form-item label="Price:">
          <el-input-number
            v-model="line.Price"
            :controls="false"
            :precision="6"
            :min="-999999"
            :max="999999"
            style="width: 70pt"
          />
          <span :style="{margin: '10px'}"><el-button type="primary" @click="line.Price = line.Price/line.Quantity">Divide by Quantity</el-button></span>
        </el-form-item>

        <el-form-item label="VAT :">
          <el-select
            v-model="line.VatTaxId"
            placeholder="VAT"
            filterable
            style="min-width: 200px; margin-right: 10px;"
          >
            <el-option v-for="item in vat" :key="item.Id" :label="item.Value +'% - '+item.Description" :value="item.Id" />
          </el-select>
        </el-form-item>
      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button type="danger" @click="visible = false, deleteLine()">Delete</el-button>
        <el-button type="primary" @click="visible = false, saveLine()">Save</el-button>
        <el-button @click="closeDialog()">Cancel</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>

import requestBN from '@/utils/requestBN'

export default {
  name: 'EditAdditionalChargesDialog',
  props: {
    line: { type: Object, default: null },
    visible: { type: Boolean, default: false },
    purchaseOrder: { type: Object, default: null }
  },
  data() {
    return {
      additionalChargeTypes: [],
      vat: []
    }
  },
  mounted() {
    this.getAdditionalChargeType()
    this.getVAT()
  },
  methods: {
    getAdditionalChargeType() {
      requestBN({
        url: '/purchasing/additionalChargeType',
        methood: 'get'
      }).then(response => {
        this.additionalChargeTypes = response.data
      })
    },
    getVAT() {
      requestBN({
        url: '/finance/tax',
        methood: 'get',
        params: {
          Type: 'VAT'
        }
      }).then(response => {
        this.vat = response.data
      })
    },
    refresh() {
      this.$emit('refresh')
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    },
    saveLine() {
      requestBN({
        method: 'post',
        url: '/purchasing/additionalCharge/edit',
        data: { data: { Action: 'save', Lines: [this.$props.line], PoNo: this.$props.purchaseOrder.PurchaseOrderNumber }}
      }).then(response => {
        if (response.error == null) {
          this.$message({
            showClose: true,
            message: 'Changes saved successfully',
            duration: 1500,
            type: 'success'
          })
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
        this.refresh()
        this.closeDialog()
      })
    },
    deleteLine() {
      this.$confirm('This will permanently delete line ' + this.line.LineNo + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        requestBN({
          method: 'post',
          url: '/purchasing/additionalCharge/edit',
          data: { data: { Action: 'delete', AdditionalChargeLineId: this.line.AdditionalChargesLineId, PoNo: this.$props.purchaseOrder.PurchaseOrderNumber }}
        }).then(response => {
          if (response.error != null) {
            this.$message({
              showClose: true,
              message: response.error,
              duration: 0,
              type: 'error'
            })
          }
          this.refresh()
          this.closeDialog()
        })
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled'
        })
      })
    }
  }
}
</script>
