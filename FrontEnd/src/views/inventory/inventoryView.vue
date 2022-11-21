<template>
  <div class="app-container">
    <h1>
      {{ inventoryData.InventoryBarcode }} - {{ inventoryData.Title }},
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
          {{ inventoryData.LocationName }}
        </p>
        <p>
          <b>Home Location:</b>
          {{ inventoryData.HomeLocationName }}
        </p>
        <p>
          <b>MAC Address Wired:</b>
          {{ inventoryData.MacAddressWired }}
        </p>
        <p>
          <b>Status:</b>
          {{ inventoryData.Status }}
        </p>
      </el-main>
      <el-main>
        <p>
          <b>Location Path:</b>
          {{ inventoryData.LocationPath }}
        </p>
        <p>
          <b>Home Location Path:</b>
          {{ inventoryData.HomeLocationPath }}
        </p>
        <p>
          <b>MAC Address Wireless:</b>
          {{ inventoryData.MacAddressWireless }}
        </p>
        <p>
          <b>Serial Number:</b>
          {{ inventoryData.SerialNumber }}
        </p>
      </el-main>
    </el-container>

    <el-divider />
    <h2>Accessories</h2>
    <template v-if="checkPermission(['inventory.accessory.add'])">
      <el-button
        type="primary"
        icon="el-icon-plus"
        circle
        style="margin-top: 00px; margin-bottom: 20px"
        @click="showEditAccessoryDialog(null)"
      />
    </template>
    <el-table :data="inventoryData.Accessory" style="width: 100%" border :cell-style="{ padding: '0', height: '20px' }">
      <el-table-column prop="AccessoryBarcode" label="Barcode" width="120" sortable />
      <el-table-column prop="Description" label="Description" sortable />
      <el-table-column prop="Note" label="Note" sortable />
      <el-table-column prop="Labeled" label="Labeled" width="120" sortable>
        <template slot-scope="{ row }">
          {{ row.Labeled }}
        </template>
      </el-table-column>
      <template v-if="checkPermission(['inventory.accessory.edit'])">
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
    <template v-if="checkPermission(['inventory.purchase.edit'])">
      <el-button
        type="primary"
        icon="el-icon-edit"
        circle
        style="margin-top: 00px; margin-bottom: 20px"
        @click="showEditPurchaseDialog(null)"
      />
    </template>
    <el-table :data="inventoryData.PurchaseInformation" style="width: 100%" border :cell-style="{ padding: '0', height: '20px' }">

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
            :to="'/supplier/supplierView/' + row.SupplierId"
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
      <b>Total Price:</b>
      {{ inventoryData.TotalPrice }} {{ inventoryData.TotalCurrency }}
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
    <template v-if="checkPermission(['inventory.history.add'])">
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
                <el-button icon="el-icon-document">{{ doc.Description }}</el-button>
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
      :inventory-number="inventoryData.InventoryNumber"
      :visible.sync="historyEditDialogVisible"
      :edit-token="historyEditToken"
      @change="getInventoryData()"
    />

    <accessoryEditDataDialog
      :inventory-number="inventoryData.InventoryNumber"
      :accessory-number="accessoryNumber"
      :visible.sync="accessoryEditDialogVisible"
      @change="getInventoryData()"
    />

    <purchaseEditDataDialog
      :inventory-number="inventoryData.InventoryNumber"
      :visible.sync="purchaseEditDataDialogVisible"
      @change="getInventoryData()"
    />

    <el-divider />
    <el-button v-if="checkPermission(['inventory.print'])" type="primary" @click="addPrint">Print</el-button>
    <el-button v-if="checkPermission(['inventory.create'])" type="primary" @click="copy">Create Copy</el-button>
  </div>
</template>

<script>
import Cookies from 'js-cookie'
import checkPermission from '@/utils/permission'
import documentsList from '@/views/document/components/listDocuments'

import historyEditDataDialog from './components/historyDialog'
import accessoryEditDataDialog from './components/accessoryDialog'
import purchaseEditDataDialog from './components/purchaseDialog'

import Inventory from '@/api/inventory'
const inventory = new Inventory()

export default {
  name: 'InventoryView',
  components: { documentsList, historyEditDataDialog, accessoryEditDataDialog, purchaseEditDataDialog },
  data() {
    return {
      inventoryData: Object.assign({}, inventory.itemReturn),

      historyEditDialogVisible: false,
      historyEditToken: null,

      accessoryEditDialogVisible: false,
      accessoryNumber: null,

      purchaseEditDataDialogVisible: false
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
    checkPermission,
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.inventoryData.InventoryBarcode}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.inventoryData.InventoryBarcode} - ${this.inventoryData.Title}`
    },
    async getInventoryData() {
      this.inventoryData = await inventory.item(this.$route.params.invNo)
    },
    showEditAccessoryDialog(accessoryNumber) {
      this.accessoryNumber = accessoryNumber
      this.accessoryEditDialogVisible = true
    },
    showEditHistoryDialog(editToken) {
      this.historyEditToken = editToken
      this.historyEditDialogVisible = true
    },
    showEditPurchaseDialog() {
      this.purchaseEditDataDialogVisible = true
    },
    addPrint() {
      var cookieList = []
      try {
        var cookiesText = Cookies.get('invNo')
        cookieList = JSON.parse(cookiesText)
      } catch (e) {
        cookieList = []
      }

      var invNoList = []
      invNoList = invNoList.concat(cookieList)

      invNoList.push(this.inventoryData.InventoryBarcode)
      Cookies.set('invNo', invNoList)

      this.$message({
        showClose: true,
        message: this.inventoryData.InventoryBarcode + ' Added to Printer Queue',
        type: 'success'
      })
    },
    copy() {
      this.$router.push(
        '/inventory/inventoryCreate/' + this.inventoryData.InventoryBarcode
      )
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
