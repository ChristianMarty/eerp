<template>
  <div class="add-to-stock-dialog">
    <el-dialog
      title="Add to Stock"
      :visible.sync="visible"
      center
      :before-close="closeDialog"
      @open="loadData()"
    >

      <el-form ref="inputForm" label-width="150px">
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

        <el-form-item label="Quantity:">
          <el-input-number v-model="data.Quantity" placeholder="Please input" :controls="false" />
        </el-form-item>

        <el-form-item label="Mfr. Date:">
          <el-date-picker v-model="data.Date" type="week" format="yyyy Week WW" value-format="yyyy-MM-dd">
            >
          </el-date-picker>
        </el-form-item>

        <el-form-item label="Lot Number:" prop="LotNumber">
          <el-input v-model="data.LotNumber" placeholder="Please input" />
        </el-form-item>

        <el-form-item label="Location:">
          <span>
            <el-input
              ref="locNrInput"
              v-model="data.LocationCode"
              placeholder="Loc-xxxxx"
              style="width: 150px; margin-right: 10px"
            />
            <el-cascader
              v-model="data.LocationCode"
              :options="locations"
              :props="{
                emitPath: false,
                value: 'LocNr',
                label: 'Name',
                children: 'Children',
                checkStrictly: true
              }"
            />
          </span>
        </el-form-item>
        <el-divider />
        <p><b>Track</b></p>
        <el-table
          ref="itemTable"
          :data="trackData"
          border
          style="width: 100%"
          :header-cell-style="{ padding: '0', height: '20px' }"
          :cell-style="{ padding: '0', height: '20px' }"
        >
          <el-table-column prop="Type" label="Type" width="120" sortable />

          <el-table-column label="Reference" sortable>
            <template slot-scope="{ row }">
              <template v-if="row.Type == 'Part Stock'">
                <router-link :to="'/stock/item/' + row.StockNo" class="link-type">
                  <span>STK-{{ row.StockNo }}</span>
                </router-link>
                <span style="float: right;"> Original Quantity: {{ row.CreateQuantity }} </span>
              </template>
              <template v-if="row.Type == 'Inventory'">
                <router-link
                  :to="'/inventory/inventoryView/' + row.InvNo"
                  class="link-type"
                >
                  <span>Inv-{{ row.InvNo }}</span>
                </router-link>
              </template>
            </template>
          </el-table-column>
        </el-table>

      </el-form>

      <span slot="footer" class="dialog-footer">

        <el-button type="primary" @click="saveToStock()">Save</el-button>
        <el-button @click="closeDialog">Close</el-button>
      </span>
    </el-dialog>  </div>
</template>

<script>

const receivedItemData = {
  ReceivalId: 0,
  ManufacturerName: '',
  ManufacturerPartNumber: '',
  SupplierName: '',
  SupplierPartNumber: '',
  QuantityReceived: 0,
  OrderReference: '',
  SupplierPartId: 0,
  LotNumber: ''
}

const saveData = {
  ReceivalId: 0,
  Date: '',
  Quantity: 0,
  LocationCode: '',
  OrderReference: '',
  LotNumber: ''
}

import Stock from '@/api/stock'
const stock = new Stock()

import Location from '@/api/location'
const location = new Location()

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'AddToStock',
  props: { receivalData: { type: Object, default: receivedItemData }, visible: { type: Boolean, default: false }},
  data() {
    return {
      data: Object.assign({}, saveData),
      locations: null,
      dateCode: '',
      trackData: null
    }
  },
  mounted() {
  },
  methods: {
    async loadData() {
      this.getTrackData()

      this.locations = await location.search()

      this.data.ReceivalId = this.$props.receivalData.ReceivalId
      this.data.Quantity = this.$props.receivalData.QuantityReceived
      this.data.OrderReference = this.$props.receivalData.OrderReference
    },
    getTrackData() {
      purchase.item.track(this.$props.receivalData.ReceivalId).then(response => {
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
    saveToStock() {
      stock.item.create(this.data).then(response => {
        this.$router.push('/stock/item/' + response.StockNo)
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
