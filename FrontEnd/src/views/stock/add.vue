<template>
  <div class="app-container">
    <h1>Add Stock</h1>
    <el-divider />
    <h2>Input Stock Number:</h2>

    <p>
      <el-input
        ref="itemNrInput"
        v-model="inputItemNr"
        placeholder="Please input"
        @keydown.enter.native="searchItem"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="searchItem"
        />
      </el-input>
    </p>
    <el-card v-if="showCard">
      <p><b>Manufacturer: </b>{{ item.ManufacturerName }}</p>
      <p><b>Part Number: </b>{{ item.ManufacturerPartNumber }}</p>
      <p><b>Date: </b>{{ item.Date }}</p>
      <p><b>Location: </b>{{ item.Location }}</p>
      <p><b>Location Path: </b>{{ item.LocationPath }}</p>
      <p><b>Stock Quantity: </b>{{ item.Quantity }}</p>
      <p>
        <b>Add Quantity: </b>
        <el-input-number
          v-model="addQuantity"
          :min="1"
          :max="100000"
        />
      </p>
      <p>
        <el-button type="primary" @click="addStock">Add</el-button>
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
      addQuantity: 0,
      inputItemNr: null,
      showCard: false,
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
          this.getProductionPartData()
          this.showCard = true
        }
      })
    },
    resetForm() {
      this.item = Object.assign({}, emptyItem)
      this.addQuantity = 0
      this.inputItemNr = null
      this.showCard = false
      this.$refs.itemNrInput.focus()
    },
    addStock() {
      requestBN({
        method: 'patch',
        url: '/stock',
        params: { StockNo: this.inputItemNr },
        data: { AddQuantity: this.addQuantity }
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
