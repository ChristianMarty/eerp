<template>
  <div class="card">
    <router-link to="/">
      <el-button size="large">Cancel</el-button>
    </router-link>

    <el-card class="box-card">
      <el-steps :active="step" finish-status="success">
        <el-step title="Select Parts"/>
        <el-step title="Checkout"/>
        <el-step title="Complete"/>
      </el-steps>

      <el-button v-if="step === 0" @click="loadCheckoutPage()">Checkout</el-button>
      <el-button v-if="step === 1" @click="loadSelectPartPage()">Select Parts</el-button>
      <el-button v-if="step === 1" @click="loadCompletePage()">Finish</el-button>

      <el-divider/>

      <template v-if="step === 0">
        <el-input
            ref="stockNumberInput"
            v-model="stockNumberInput"
            placeholder="Stock Code (STK-xxxx)"
            @keyup.enter.native="searchInput()"
        >
          <el-button slot="append" icon="el-icon-search" @click="searchInput()"/>
        </el-input>

        <p>
          <el-table
              :data="itemList"
              border
              style="width: 100%"
              @row-click="(row:RemoveStockItem) =>openSetQuantity(row)"
          >
            <el-table-column prop="ItemCode" label="Item Code" width="100"/>
            <el-table-column label="Description">
              <template #default="scope">
                <span> {{ scope.row.Part.ManufacturerName }} {{ scope.row.Part.ManufacturerPartNumber }} </span>
              </template>
            </el-table-column>
            <el-table-column prop="Quantity.Quantity" label="Stock Quantity" width="130"/>
            <el-table-column prop="RemoveQuantity" label="Remove Quantity" width="150"/>
          </el-table>
        </p>
      </template>

      <template v-if="step === 1">
        <el-form label-width="150px">
          <el-form-item label="Work Order:">

            <!-- <el-select v-model="selectedWorkOrderNumber" filterable>
                <el-option
                  v-for="wo in workOrders"
                  :key="wo.WorkOrderNumber"
                  :label="wo.WorkOrderBarcode + ' - ' + wo.Title"
                  :value="wo.WorkOrderNumber"
                />
              </el-select>
              <el-button type="primary" @click="workOrderId = null">Clear</el-button> -->

          </el-form-item>
        </el-form>
      </template>

      <template v-if="step === 2">
        <StockRemoveCompleted :data="removeResult"></StockRemoveCompleted>
      </template>

      <!--  <template v-if="step === 3">
          <p><b>Work Order:</b> {{ selectedWorkOrderData.WorkOrderBarcode }}  {{ selectedWorkOrderData.Title }}</p>
        </template>

        <h2>Items</h2>

        <template v-if="step === 3">
          <el-select v-model="selectedPrinterId">
            <el-option v-for="item in printer" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
          </el-select>
          <el-button @click="printReceipt()">Print Receipt</el-button>
          <el-button @click="printAllNotes()">Print All Part Notes </el-button>
        </template>
      -->



    </el-card>

    <el-dialog
        title="Remove Quantity"
        v-model="quantityDialogVisible"
        width="50%"
    >

      <el-form label-width="150px">
        <el-form-item label="Item Code:">
          {{ currentItem.ItemCode }}
        </el-form-item>
        <el-form-item label="Part:">
          {{ currentItem.Part.ManufacturerName }} - {{ currentItem.Part.ManufacturerPartNumber }}
        </el-form-item>
        <el-form-item label="Remove Quantity:">
          <el-input-number
              ref="stockRemoveQuantityInput"
              v-model="currentItem.RemoveQuantity"
              :min="1"
              :max="Number(currentItem.Quantity.Quantity)"
              @keyup.enter.native="confirmQuantityAndClose()"
          />
        </el-form-item>
        <el-form-item label="Current Stock:">
          {{ currentItem.Quantity.Quantity }}
        </el-form-item>
        <el-form-item label="Note:">
          <el-input v-model="currentItem.Note" type="textarea"/>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="confirmQuantityAndClose()">Confirm</el-button>
          <el-button @click="quantityDialogVisible = false">Cancel</el-button>
        </el-form-item>
      </el-form>

    </el-dialog>
  </div>
</template>

<script lang="ts">
// @ is an alias to /src

import {ElMessage} from 'element-plus'
import {isProxy, toRaw} from 'vue';

import Stock, {StockItem, RemoveStockItem, BulkRemoveResult} from '../../api/stock'
const stock = new Stock()

class ExtendedStockItem extends StockItem {
  RemoveQuantity: number = 1
  Note: string = ""
}

export default {
  name: 'RemoveStock',
  data() {
    return {
      stockNumberInput: "" as string,
      step: 0 as number,
      quantityDialogVisible: false as boolean,
      selectedWorkOrderNumber: null as string | null,
      workOrders: [],
      currentItem: {} as ExtendedStockItem,
      itemList: [] as ExtendedStockItem[],
      removeResult: [] as BulkRemoveResult[],
    }
  },
  mounted() {
    this.$refs.stockNumberInput.focus()
  },
  methods: {
    async searchInput() {
      const itemData = await stock.get(this.stockNumberInput)
      // if not already in list
      if (!this.itemList.some((element) => element.ItemCode === itemData.ItemCode)) {
        this.openSetQuantity(itemData)
      } else {
        this.$refs.stockNumberInput.focus()
        ElMessage({
          showClose: true,
          message: 'This item is already in the list.',
          type: 'warning'
        })
      }
      this.stockNumberInput = ''
    },
    openSetQuantity(data: StockItem | ExtendedStockItem) {
      if (this.step !== 0) return
      this.quantityDialogVisible = true
      if (isProxy(data)) {
        this.currentItem = structuredClone(toRaw(data)) as ExtendedStockItem
      } else {
        this.currentItem = data as ExtendedStockItem
        this.currentItem.RemoveQuantity = 1
        this.currentItem.Note = ""
      }

      this.$nextTick(() => {
        this.$refs.stockRemoveQuantityInput.focus()
      })
    },
    confirmQuantityAndClose() {
      this.quantityDialogVisible = false
      console.log(this.currentItem)

      if (!this.itemList.some((element) => element.ItemCode === this.currentItem.ItemCode)) {
        this.itemList.unshift(this.currentItem)
      } else {
        let found = this.itemList.find((element) => element.ItemCode === this.currentItem.ItemCode)
        found = this.currentItem
        console.log("asd")
      }
      this.$refs.stockNumberInput.focus()
    },
    async remove() {
      const bulkRemove = [] as RemoveStockItem[]

      this.itemList.forEach(item => {
        bulkRemove.push({
          ItemCode: item.ItemCode,
          Note: item.Note,
          RemoveQuantity: item.RemoveQuantity
        })
      })
      stock.bulkRemove(bulkRemove,null).then(response => {
        this.removeResult = response
        console.log(response)
      }).catch(response => {
        ElMessage({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    },
    loadCheckoutPage() {
      this.step = 1
    },
    loadSelectPartPage() {
      this.step = 0
    },
    loadCompletePage() {
      this.step = 2
      this.remove()
    }
  }
}
</script>

<style scoped>
.box-card {
  width: 100%;
  height: 100%;
  margin-top: 20px;
}
</style>
