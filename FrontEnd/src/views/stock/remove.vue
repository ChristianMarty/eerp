<template>
  <div class="app-container">
    <h1>Remove Stock</h1>
    <el-divider />
    <h2>Input Stock Number:</h2>
    <p>
      <el-input
        ref="itemNrInput"
        v-model="inputItemNr"
        placeholder="Please input"
        @keyup.enter.native="searchItem"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="searchItem"
        />
      </el-input>
    </p>
    <el-card v-if="showRemove">
      <p><b>Manufacturer: </b>{{ item.ManufacturerName }}</p>
      <p><b>Part Number: </b>{{ item.ManufacturerPartNumber }}</p>
      <p><b>Date: </b>{{ item.Date }}</p>
      <p><b>Location: </b>{{ item.Location }}</p>
      <p><b>Location Path: </b>{{ item.LocationPath }}</p>
      <p><b>Stock Quantity: </b>{{ item.Quantity }}</p>
      <p>
        <b>Remove Quantity: </b>
        <el-input-number
          v-model="removeQuantity"
          :min="1"
          :max="quantity"
        />
      </p>
      <p>
        <el-button type="primary" @click="removeStock">Remove</el-button>
        <el-button type="danger" @click="resetForm">Cancel</el-button>
      </p>

      <h3>Production Parts</h3>
      <el-table :data="productionPartData" style="width: 100%">
        <el-table-column prop="PartNo" label="Part No" sortable width="100">
          <template slot-scope="{ row }">
            <router-link
              :to="'/prodParts/prodPartView/' + row.PartNo"
              class="link-type"
            >
              <span>{{ row.PartNo }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Description" label="Description" sortable />
      </el-table>
    </el-card>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

const emptyItem = {
  StockId: '',
  Manufacturer: '',
  ManufacturerPartNumber: '',
  Date: '',
  Quantity: '',
  Location: ''
}

export default {
  name: 'RemoveFromStock',
  components: {},
  data() {
    return {
      item: Object.assign({}, emptyItem),
      removeQuantity: 0,
      quantity: 0,
      inputItemNr: null,
      showRemove: false,
      productionPartData: null
    }
  },
  mounted() {
    this.$refs.itemNrInput.focus()
  },
  methods: {
    searchItem() {
      requestBN({
        url: '/stock',
        methood: 'get',
        params: { StockNo: this.inputItemNr }
      }).then(response => {
        if (response.error != null) {
          this.$message({
            showClose: true,
            message: response.error,
            duration: 0,
            type: 'error'
          })
        } else if (response.data.length == 0) {
          this.$message({
            showClose: true,
            message: 'Item dose not exist!',
            type: 'warning'
          })
        } else {
          this.item = response.data[0]
          this.quantity = this.item.Quantity
          this.showRemove = true
          this.getProductionPartData()
        }
      })
    },
    resetForm() {
      this.item = Object.assign({}, emptyItem)
      this.removeQuantity = 0
      this.quantity = 0
      this.inputItemNr = null
      this.showRemove = false
      this.$refs.itemNrInput.focus()
    },
    removeStock() {
      requestBN({
        method: 'patch',
        url: '/stock',
        params: { StockNo: this.inputItemNr },
        data: { RemoveQuantity: this.removeQuantity }
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

          this.resetForm()
        }
      })
    },
    getProductionPartData() {
      requestBN({
        url: '/productionPart',
        methood: 'get',
        params: { ManufacturerPartId: this.item.ManufacturerPartId }
      }).then(response => {
        this.productionPartData = response.data
      })
    }
  }
}
</script>
