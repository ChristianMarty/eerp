<template>
  <div class="app-container">
    <h1>
      {{ inventoryData.ItemCode }} - {{ inventoryData.Title }},
      {{ inventoryData.ManufacturerName }}
      {{ inventoryData.Type }}
    </h1>
    <el-divider />

    <el-container>
      <el-aside>
        <el-image style="width: 250px;" :src="inventoryData.PicturePath" :fit="fit" />
      </el-aside>
      <el-main>
        <p>
          <b>Location:</b>
          {{ inventoryData.Location.Name }}
        </p>
        <p>
          <b>Location Path:</b>
          {{ inventoryData.Location.Path }}
        </p>
        <p>
          <b>Status:</b>
          {{ inventoryData.Status }}
        </p>
        <p>
          <b>Category:</b>
          {{ inventoryData.CategoryName }}
        </p>
      </el-main>
      <el-main>
        <p>
          <b>Home Location:</b>
          {{ inventoryData.Location.HomeName }}
        </p>
        <p>
          <b>Home Location Path:</b>
          {{ inventoryData.Location.HomePath }}
        </p>
        <p>
          <b>Serial Number:</b>
          {{ inventoryData.SerialNumber }}
        </p>
      </el-main>
    </el-container>
    <el-button v-permission="['Location_Transfer']" type="primary" @click="showLocationTransferDialog()">Location Transfer</el-button>
    <el-divider />
    <h2>Attributes</h2>
    <el-table :data="inventoryData.Attribute" style="width: 100%" border :cell-style="{ padding: '0', height: '20px' }">
      <el-table-column prop="Name" label="Name" sortable />
      <el-table-column prop="Value" label="Value" sortable />
    </el-table>

    <el-divider />
    <h2>Accessories</h2>
    <template v-permission="['Inventory_Accessory_Edit']">
      <el-button
        type="primary"
        icon="el-icon-plus"
        circle
        style="margin-top: 00px; margin-bottom: 20px"
        @click="showEditAccessoryDialog(null)"
      />
    </template>
    <el-table :data="inventoryData.Accessory" style="width: 100%" border :cell-style="{ padding: '0', height: '20px' }">
      <el-table-column prop="ItemCode" label="Barcode" width="120" sortable />
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="Note" label="Note" sortable />
      <el-table-column prop="Labeled" label="Labeled" width="120" sortable>
        <template slot-scope="{ row }">
          {{ row.Labeled }}
        </template>
      </el-table-column>
      <template v-permission="['Inventory_Accessory_Edit']">
        <el-table-column label="Edit" width="50">
          <template slot-scope="{ row }">
            <el-button
              size="mini"
              type="primary"
              icon="el-icon-edit"
              circle
              style="margin-top: 5px; margin-bottom: 5px"
              @click="showEditAccessoryDialog(row.AccessoryNumber)"
            />
          </template>
        </el-table-column>
      </template>
    </el-table>

    <el-divider />
    <h2>Purchase Information</h2>
    <template v-permission="['Inventory_Accessory_Edit']">
      <el-button
        type="primary"
        icon="el-icon-edit"
        circle
        style="margin-top: 00px; margin-bottom: 20px"
        @click="showEditPurchaseDialog(null)"
      />
    </template>
    <el-table :data="inventoryData.PurchaseInformation.Item" style="width: 100%" border :cell-style="{ padding: '0', height: '20px' }">
      <el-table-column prop="PurchaseOrderBarcode" label="Po No" width="140" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/purchasing/edit/' + row.PurchaseOrderBarcode"
            class="link-type"
          >
            <span>{{ row.PurchaseOrderBarcode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="CostType" label="Type" width="140" sortable />
      <el-table-column prop="Quantity" label="Quantity" width="120" sortable />
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="Price" label="Price" width="140" align="right" header-align="left">
        <template slot-scope="{ row }">
          {{ row.Price }} {{ row.Currency }}
        </template>
      </el-table-column>
      <el-table-column prop="PurchaseDate" label="Date" width="140" sortable />
      <el-table-column prop="Supplier" label="Supplier" width="200" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/vendor/view/' + row.SupplierId"
            class="link-type"
          >
            <span>{{ row.SupplierName }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="SupplierPartNumber" label="Part Number" width="200" sortable />
      <el-table-column prop="OrderReference" label="Order Reference" width="200" sortable />
    </el-table>

    <p>
      <b>Total Purchase Cost:</b>
      {{ inventoryData.PurchaseInformation.Total.PurchaseCost }} {{ inventoryData.PurchaseInformation.Total.Currency }}
    </p>
    <p>
      <b>Total Maintenance Cost:</b>
      {{ inventoryData.PurchaseInformation.Total.MaintenanceCost }} {{ inventoryData.PurchaseInformation.Total.Currency }}
    </p>
    <p>
      <b>Total Cost of Ownership:</b>
      {{ inventoryData.PurchaseInformation.Total.CostOfOwnership }} {{ inventoryData.PurchaseInformation.Total.Currency }}
    </p>

    <el-divider />
    <h2>Description</h2>
    <pre>
      <div class="Description-content" v-html="inventoryData.Description" />
    </pre>
    <el-divider />
    <h2>Note</h2>
    <pre>
      <div class="Note-content" v-html="inventoryData.Note" />
    </pre>
    <el-divider />
    <h2>Documents</h2>
    <documentsList :documents="inventoryData.Documents" />
    <h2>History</h2>
    <template v-permission="['Inventory_History_Create']">
      <el-button
        type="primary"
        icon="el-icon-plus"
        circle
        style="margin-top: 20px; margin-bottom: 20px"
        @click="showEditHistoryDialog(null)"
      />
    </template>
    <el-timeline reverse="true">
      <el-timeline-item
        v-for="(line, index) in inventoryData.History"
        :key="index"
        :color="line.color"
        :timestamp="line.Date"
        placement="top"
      >
        <el-card>
          <el-col class="line" :span="20">
            <b>{{ line.Type }}</b>
            <p>{{ line.Description }}</p>
            <p v-for="(doc, index2) in line.Documents" :key="index2">
              <a :href="doc.Path" target="blank">
                <el-button icon="el-icon-document">{{ doc.Name }}</el-button>
              </a>
            </p>
            <p v-if="line.NextDate">Next {{ line.Type }}: {{ line.NextDate }}</p>
          </el-col>
          <el-col class="line" :span="4">
            <template v-if="line.EditToken != NULL">
              <el-button style="margin: 20px" type="primary" icon="el-icon-edit" circle @click="showEditHistoryDialog(line.EditToken)" />
            </template>
          </el-col>
        </el-card>
      </el-timeline-item>
    </el-timeline>

    <historyEditDataDialog
      :inventory-number="inventoryData.ItemCode"
      :visible.sync="historyEditDialogVisible"
      :edit-token="historyEditToken"
      @change="getInventoryData()"
    />

    <accessoryEditDataDialog
      :inventory-number="inventoryData.ItemCode "
      :accessory-number="accessoryCode"
      :visible.sync="accessoryEditDialogVisible"
      @change="getInventoryData()"
    />

    <purchaseEditDataDialog
      :inventory-number="inventoryData.ItemCode"
      :visible.sync="purchaseEditDataDialogVisible"
      @change="getInventoryData()"
    />

    <locationTransferDialog
      :barcode="inventoryData.ItemCode"
      :visible.sync="locationTransferDialogVisible"
      @change="getInventoryData()"
    />

    <el-divider />
    <el-button v-permission="['Inventory_Create']" type="primary" @click="copy">Create Copy</el-button>
  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'
import documentsList from '@/views/document/components/documentsList'

import historyEditDataDialog from './components/historyDialog'
import accessoryEditDataDialog from './components/accessoryDialog'
import purchaseEditDataDialog from './components/purchaseDialog'

import locationTransferDialog from '@/components/Location/locationTransferDialog'

import Inventory from '@/api/inventory'
const inventory = new Inventory()

export default {
  name: 'InventoryView',
  components: { documentsList, historyEditDataDialog, accessoryEditDataDialog, purchaseEditDataDialog, locationTransferDialog },
  directives: { permission },
  data() {
    return {
      inventoryData: Object.assign({}, inventory.itemReturn),

      historyEditDialogVisible: false,
      historyEditToken: null,

      accessoryEditDialogVisible: false,
      accessoryCode: null,

      purchaseEditDataDialogVisible: false,

      locationTransferDialogVisible: false
    }
  },
  async mounted() {
    await this.getInventoryData()
    this.setTitle()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.inventoryData.ItemCode}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.inventoryData.ItemCode} - ${this.inventoryData.Title}`
    },
    showLocationTransferDialog() {
      this.locationTransferDialogVisible = true
    },
    async getInventoryData() {
      this.inventoryData = await inventory.item(this.$route.params.invNo)
    },
    showEditAccessoryDialog(accessoryCode) {
      if (accessoryCode === null) this.accessoryCode = this.inventoryData.ItemCode
      else this.accessoryCode = accessoryCode
      this.accessoryEditDialogVisible = true
    },
    showEditHistoryDialog(editToken) {
      this.historyEditToken = editToken
      this.historyEditDialogVisible = true
    },
    showEditPurchaseDialog() {
      this.purchaseEditDataDialogVisible = true
    }
  }
}
</script>

<style scoped>
.el-aside {
  background-color: #ffffff;
  color: #333;
  text-align: center;
}

.el-main {
  color: #333;
}
</style>
