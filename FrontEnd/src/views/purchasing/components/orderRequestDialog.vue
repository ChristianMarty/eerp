<template>
  <div class="add-to-stock-dialog">

    <el-dialog title="Order Request" :visible.sync="visible" :before-close="closeDialog" @open="loadData()">
      <el-form
        ref="inputForm"
        :model="receivalData"
        class="form-container"
        label-width="150px"
      >
        <el-form-item label="Quantity:" prop="Quantity">
          <el-input-number
            v-model="quantity"
            :min="1"
          />
        </el-form-item>
        <el-form-item label="Description:" prop="Description">
          <el-input
            v-model="description"
          />
        </el-form-item>

      </el-form>

      <span slot="footer" class="dialog-footer">
        <el-button @click="closeDialog">Close</el-button>
        <el-button
          type="primary"
          @click="sendOrderRequest()"
        >Save</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>

import requestBN from '@/utils/requestBN'

export default {
  name: 'AddToStock',
  props: { supplierPartId: { type: Number, default: 0 }, visible: { type: Boolean, default: false }},
  data() {
    return {
      quantity: 0,
      description: ''
    }
  },
  mounted() {

  },
  methods: {
    loadData(){

    },
    sendOrderRequest() {
      const orderRequestData = {
        SupplierPartId: this.$props.supplierPartId,
        Quantity: this.quantity,
        Description: this.description
      }

      requestBN({
        method: 'post',
        url: '/purchasing/orderRequest',
        data: { data: orderRequestData }
      }).then(response => {
        if (response.error == null) {
          // this.partData = response.data
        // this.$router.push('/stock/item/' + this.partData.StockNo)
        } else {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        }
      })
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
    }
  }
}
</script>
