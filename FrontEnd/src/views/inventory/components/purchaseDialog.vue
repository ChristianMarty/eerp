<template>
  <div class="inventory-purchase-dialog">
    <el-dialog
      title="Purchase Information Edit"
      :visible.sync="visible"
      :before-close="closeDialog"
      center
      width="75%"
      @open="onOpen()"
    >
      <el-input ref="poSearchInput" v-model="poSearchInput" placeholder="PO Search" @keyup.enter.native="searchPo()">
        <el-button slot="append" icon="el-icon-search" @click="searchPo()" />
      </el-input>
      <p />
      <p v-if="poData.MetaData !== undefined">{{ poData.MetaData.SupplierName }} - {{ poData.MetaData.Title }}</p>
      <el-table
        :data="poData.Lines"
        style="width: 100%"
        border
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="ReceivalId"
        :tree-props="{ children: 'Received' }"
      >
        <el-table-column prop="PurchaseOrderBarcode" label="PO Number" width="140" sortable />

        <el-table-column prop="QuantityReceived" label="Quantity" width="120" sortable />
        <el-table-column prop="Description" label="Description" sortable />

        <el-table-column width="55">
          <template v-if="row.ReceivalId != NULL" slot-scope="{ row }">
            <el-popover
              :title="row.PurchaseOrderBarcode"
              placement="left"
              width="400"
              trigger="click"
            >
              <el-form>
                <!-- <el-form-item label="ReceivalId">{{ row.ReceivalId }}</el-form-item> -->
                <el-form-item label="Quantity">
                  <el-input-number v-model="addQuantity" controls-position="right" :min="1" :max="10" @change="handleChange" />
                </el-form-item>
                <el-form-item label="Type">
                  <el-select v-model="addCostType" controls-position="right" @change="handleChange">
                    <el-option
                      v-for="item in costType"
                      :key="item"
                      :label="item"
                      :value="item"
                    />
                  </el-select>
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="addItem(row)">Add</el-button>
                </el-form-item>
              </el-form>

              <el-button slot="reference" icon="el-icon-plus" type="primary" circle size="mini" :style="{ margin: '5px'}" />
            </el-popover>
          </template>
        </el-table-column>
      </el-table>
      <p><b>Note:</b> If the add (+) button is missing, the item hasn't been received yet. </p>

      <el-divider />
      <p><b>Selected:</b></p>
      <span>
        <el-popover
          v-for="tag in purchaseData"
          :key="tag.PurchaseOrderBarcode"
          placement="top-start"
          width="200"
          trigger="hover"
        >
          <p><b>{{ tag.SupplierName }} -  {{ tag.SupplierPartNumber }}</b></p>
          <p>{{ tag.Description }}</p>
          <p><b>Type: </b>{{ tag.CostType }}</p>
          <p><b>Quantity: </b>{{ tag.Quantity }}</p>

          <el-tag
            slot="reference"
            style="margin: 5px"
            closable
            :disable-transitions="false"
            @close="handleClose(tag)"
          >
            {{ tag.PurchaseOrderBarcode }}
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

import Inventory from '@/api/inventory'
const inventory = new Inventory()

import Purchase from '@/api/purchase'
const purchase = new Purchase()

export default {
  name: 'InventoryPurchaseData',
  props: {
    inventoryNumber: { type: String, default: '' },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      purchaseData: [],
      poSearchInput: '',
      addQuantity: 1,
      addCostType: null,
      poData: {},
      costType: []
    }
  },
  mounted() {
  },
  methods: {
    async onOpen() {
      // this.poData = []
      // this.poSearchInput = ''
      this.purchaseData = await inventory.purchase.search(this.$props.inventoryNumber)
      this.costType = await inventory.purchase.type()
      this.addCostType = this.costType[0]
    },
    searchPo() {
      purchase.item.search(this.poSearchInput).then(response => {
        this.poData = response
        this.prepairLines(this.poData.Lines)
      })
    },
    addItem(row) {
      const data = {}

      data.SupplierName = this.poData.MetaData.SupplierName
      data.SupplierPartNumber = row.SupplierSku
      data.Description = row.Description
      data.PurchaseOrderBarcode = row.PurchaseOrderBarcode
      data.Quantity = this.addQuantity
      data.CostType = this.addCostType
      data.ReceivalId = row.ReceivalId

      this.purchaseData.push(data)
      this.addQuantity = 0
    },
    save() {
      inventory.purchase.save(this.$props.inventoryNumber, this.purchaseData).then(response => {
        this.closeDialog()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 1500,
          type: 'error'
        })
      })
    },
    prepairLines(data) {
      data.forEach(line => {
        line.lineKey = line.LineNo

        if ('Received' in line) {
          if (line.Received.length === 1) {
            line.ReceivalDate = line.Received[0].ReceivalDate
            line.ReceivalId = line.Received[0].ReceivalId
            delete line.Received
          } else {
            let i = 0
            line.Received.forEach(subLine => {
              i++
              subLine.lineKey = line.lineKey + '.' + i
              subLine.StockPart = line.StockPart
              subLine.LineType = line.LineType
            })
          }
        }
      })
    },
    handleClose(tag) {
      this.purchaseData.splice(this.purchaseData.indexOf(tag), 1)
    },
    closeDialog() {
      this.visible = false
      this.$emit('update:visible', this.visible)
      this.$emit('change')
    }
  }
}
</script>
