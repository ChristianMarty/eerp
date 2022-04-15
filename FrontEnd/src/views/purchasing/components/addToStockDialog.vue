<template>
  <div class="add-to-stock-dialog">

    <el-dialog title="Add to Stock" :visible.sync="visible" :before-close="closeDialog" @open="loadData()">
      <el-form ref="inputForm" :model="receivalData" class="form-container" label-width="150px">
        <el-form-item label="Manufacturer:" prop="ManufacturerName">
          {{ receivalData.ManufacturerName }}
        </el-form-item>
        <el-form-item label="Part Number:" prop="ManufacturerPartNumber">
          {{ receivalData.ManufacturerPartNumber }}
        </el-form-item>

        <el-form-item label="Supplier:" prop="SupplierName">
          {{ receivalData.SupplierName }}
        </el-form-item>

        <el-form-item label="Sku:" prop="SupplierPartNumber">
          {{ receivalData.SupplierPartNumber }}
        </el-form-item>

        <el-form-item label="Order Reference:" prop="OrderReference">
          {{ receivalData.OrderReference }}
        </el-form-item>

        <el-form-item label="Quantity:" prop="QuantityReceived">
          <el-input-number v-model="quantity" placeholder="Please input" :controls="false" />
        </el-form-item>

        <el-form-item label="Mfr. Date:" prop="Date">
          <el-date-picker v-model="dateCode" type="week" format="yyyy Week WW" value-format="yyyy-MM-dd">
            >
          </el-date-picker>
        </el-form-item>

        <el-form-item label="Location:" prop="Location">
          <span>
            <el-input ref="locNrInput" v-model="locationNo" placeholder="Loc-xxxxx"
              style="width: 150px; margin-right: 10px" />
            <el-cascader v-model="locationNo" :options="locations" :props="{
              emitPath: false,
              value: 'LocNr',
              label: 'Name',
              children: 'Children',
              checkStrictly: true
            }" />
          </span>
        </el-form-item>

      </el-form>

      <span slot="footer" class="dialog-footer">

        <el-button type="primary" @click="saveToStock()">Save</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>  </div>
</template>

<script>

const receivedItemData = {
  ManufacturerName: '',
  ManufacturerPartNumber: '',
  SupplierName: '',
  SupplierPartNumber: '',
  QuantityReceived: 0,
  OrderReference: ''
}

import requestBN from '@/utils/requestBN'

export default {
  name: 'AddToStock',
  props: { receivalId: { type: Number, default: 0 }, visible: { type: Boolean, default: false } },
  data() {
    return {
      receivalData: Object.assign({}, receivedItemData),
      locations: null,
      locationNo: null,
      dateCode: '',
      quantity: 0

    }
  },
  mounted() {
    this.getLocations()
    this.getReceived()
  },
  methods: {
    loadData() {
      this.getReceived()
    },
    getLocations() {
      requestBN({
        url: '/location',
        methood: 'get'
      }).then(response => {
        this.locations = response.data
      })
    },
    getReceived() {
      requestBN({
        url: 'purchasing/item/received',
        methood: 'get',
        params: {
          ReceivalId: this.$props.receivalId
        }
      }).then(response => {
        this.receivalData = response.data
        this.quantity = this.receivalData.QuantityReceived
      })
    },
    saveToStock() {
      const saveData = {
        ReceivalId: this.$props.receivalId,
        Date: this.dateCode,
        Quantity: this.quantity,
        Location: this.locationNo,
        OrderReference: this.receivalData.OrderReference
      }

      requestBN({
        method: 'post',
        url: '/stock',
        data: { data: saveData }
      }).then(response => {
        if (response.error == null) {
          this.partData = response.data
          this.$router.push('/stock/item/' + this.partData.StockNo)
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
