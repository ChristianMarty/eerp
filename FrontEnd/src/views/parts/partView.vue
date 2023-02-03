<template>
  <div class="app-container">
    <h1>
      {{ partData.ManufacturerName }}
      {{ partData.ManufacturerPartNumber }}
    </h1>
    <h2>{{ partData.Description }} </h2>
    <el-divider />
    <div class="components-container">
      <split-pane split="vertical" :default-percent="90" @resize="resize">
        <template slot="paneL">
          <el-container>
            <el-aside width="100%" style="background-color:white">
              <div class="left-container">
                <p>
                  <b>Manufacturer:</b>
                  {{ partData.ManufacturerName }}
                </p>
                <p>
                  <b>Part Number:</b>
                  {{ partData.ManufacturerPartNumber }}
                </p>
                <p>
                  <b>Category:</b>
                  {{ partData.PartClassName }}
                </p>
                <p>
                  <b>Package:</b>
                  {{ partData.Package }}
                </p>
                <p>
                  <b>Lifecycle Status:</b>
                  {{ partData.Status }}
                </p>
                <p>
                  <b>Total Stock Quantity:</b>
                  {{ partData.StockQuantity }}
                </p>
                <el-collapse @change="handleChange">
                  <el-collapse-item name="elChar">
                    <template slot="title">
                      <b>Electrical Characteristics</b>
                    </template>
                    <el-table
                      :data="partData.PartData"
                      :default-sort="{ prop: 'Package', order: 'descending' }"
                      style="width: 100%"
                    >
                      <el-table-column prop="Name" width="200px" />
                      <el-table-column prop="Value.Minimum" label="Minimum" align="center" />
                      <el-table-column prop="Value.Typical" label="Typical" align="center" />
                      <el-table-column prop="Value.Maximum" label="Maximum" align="center" />
                      <el-table-column prop="Symbol" label="Unit" />
                    </el-table>
                    <el-button
                      v-permission="['manufacturerPart.edit']"
                      type="primary"
                      icon="el-icon-edit"
                      circle
                      style="margin-top: 20px"
                      @click="attributeEditVisible = true"
                    />

                    <attributeEdit :part-id="partData.partId" :visible.sync="attributeEditVisible" />
                  </el-collapse-item>

                  <el-collapse-item name="documents">
                    <template slot="title">
                      <b>Documents</b>
                    </template>
                    <part-documents
                      v-for="row in documents"
                      :key="row.Id"
                      :data="row"
                      @changeDocument="documentPath = $event"
                    />
                  </el-collapse-item>
                  <el-collapse-item name="suppliers">
                    <template slot="title">
                      <b>Suppliers</b>
                    </template>
                    <el-table :data="supplierPartData" style="width: 100%; margin-top:10px">
                      <el-table-column prop="SupplierName" label="Supplier Name" sortable />
                      <el-table-column label="Part Number" sortable>
                        <template slot-scope="{ row }">
                          <a
                            :href="row.SupplierPartLink"
                            target="blank"
                          >{{ row.SupplierPartNumber }}</a>
                        </template>
                      </el-table-column>
                      <el-table-column prop="Note" label="Note" sortable />
                      <el-table-column>
                        <template slot-scope="{ row }">
                          <el-button
                            style="float: right;"
                            type="text"
                            size="mini"
                            @click="orderReqestSupplierPartId = row.SupplierPartId, orderReqestDialogVisible = true"
                          >Request Order</el-button>
                        </template>
                      </el-table-column>
                    </el-table>
                    <el-button
                      v-permission="['supplierPart.create']"
                      type="primary"
                      icon="el-icon-plus"
                      circle
                      style="margin-top: 20px"
                      @click="createSupplierPartDialogVisible = true"
                    />
                    <createSupplierPartDialog
                      :visible.sync="createSupplierPartDialogVisible"
                      :manufacturer-part-id="partData.PartId"
                      @update:visible="getSupplierPart()"
                    />
                    <orderReqestDialog
                      :visible.sync="orderReqestDialogVisible"
                      :supplier-part-id="orderReqestSupplierPartId"
                    />
                  </el-collapse-item>
                  <el-collapse-item name="orderRequest">
                    <template slot="title">
                      <b>Order Requests</b>
                    </template>

                    <el-table :data="orderRequests" style="width: 100%; margin-top:10px">
                      <el-table-column prop="SupplierName" label="Supplier" sortable width="150" />

                      <el-table-column prop="SupplierPartNumber" label="Part Number" sortable>
                        <template slot-scope="{ row }">
                          <a
                            :href="row.SupplierPartLink"
                            target="blank"
                          >{{ row.SupplierPartNumber }}</a>
                        </template>
                      </el-table-column>

                      <el-table-column prop="Quantity" label="Quantity" width="120" sortable />

                      <el-table-column
                        prop="CreationDate"
                        label="Creation Date"
                        width="170"
                        sortable
                      />
                    </el-table>
                  </el-collapse-item>
                  <el-collapse-item name="purchaseOrder">
                    <template slot="title">
                      <b>Purchase Order</b>
                    </template>
                    <el-table :data="purchaseOrderData" style="width: 100%; margin-top:10px">
                      <el-table-column prop="PoNo" label="PO Number" width="150" sortable>
                        <template slot-scope="{ row }">
                          <router-link :to="'/purchasing/edit/' + row.PoNo" class="link-type">
                            <span>PO-{{ row.PoNo }}</span>
                          </router-link>
                        </template>
                      </el-table-column>
                      <el-table-column prop="Title" label="PO Title" sortable />
                      <el-table-column prop="Sku" label="Sku" sortable />
                      <el-table-column prop="Quantity" label="Quantity" sortable width="120" />
                      <el-table-column prop="Price" label="Price" sortable width="100" />
                      <el-table-column prop="Status" label="Status" sortable width="100" />
                    </el-table>
                    <p>
                      <b>Total Order Quantity:</b>
                      {{ purchaseOrder.TotalOrderQuantity }}
                    </p>
                    <p>
                      <b>Pending Order Quantity:</b>
                      {{ purchaseOrder.PendingOrderQuantity }}
                    </p>
                  </el-collapse-item>
                  <el-collapse-item name="availability">
                    <template slot="title">
                      <b>Availability</b>
                    </template>
                    <template v-if="availabilityData != null">
                      <el-table
                        :data="availabilityData.Data"
                        border
                        style="width: 100%; margin-top:10px"
                      >
                        <el-table-column prop="Name" label="Distributor" width="150" sortable />
                        <el-table-column prop="SKU" label="SKU">
                          <template slot-scope="{ row }">
                            <a :href="row.URL" target="blank">{{ row.SKU }}</a>
                          </template>
                        </el-table-column>
                        <el-table-column prop="Stock" label="Stock" width="80" sortable />
                        <el-table-column
                          prop="MinimumOrderQuantity"
                          label="MOQ"
                          width="80"
                          sortable
                        />
                        <el-table-column prop="LeadTime" label="LeadTime" width="120" sortable />
                      </el-table>
                      <p>
                        <b>Timestamp:</b>
                        {{ availabilityData.Timestamp }}, Data
                        provided by Octopart
                      </p>
                    </template>
                  </el-collapse-item>
                  <el-collapse-item name="productionPart">
                    <template slot="title">
                      <b>Production Parts</b>
                    </template>
                    <el-table :data="productionPartData" style="width: 100%">
                      <el-table-column prop="ProductionPartNumber" label="Part No" sortable width="100">
                        <template slot-scope="{ row }">
                          <router-link
                            :to="'/prodParts/prodPartView/' + row.ProductionPartNumber"
                            class="link-type"
                          >
                            <span>{{ row.ProductionPartNumber }}</span>
                          </router-link>
                        </template>
                      </el-table-column>
                      <el-table-column prop="Description" label="Description" sortable />
                    </el-table>
                  </el-collapse-item>
                  <el-collapse-item name="stock">
                    <template slot="title">
                      <b>Stock</b>
                    </template>

                    <el-table :data="stockData" style="width: 100%">
                      <el-table-column prop="StockNo" label="Stock No" sortable>
                        <template slot-scope="{ row }">
                          <router-link :to="'/stock/item/' + row.StockNo" class="link-type">
                            <span>{{ row.StockNo }}</span>
                          </router-link>
                        </template>
                      </el-table-column>

                      <el-table-column prop="Date" label="Date" sortable />
                      <el-table-column prop="Quantity" label="Quantity" sortable />
                      <el-table-column prop="Location" label="Location" sortable />
                    </el-table>
                  </el-collapse-item>
                </el-collapse>
              </div>
            </el-aside>
          </el-container>
        </template>
        <template v-if="documentPath != null" slot="paneR">
          <div class="right-container">
            <iframe :src="documentPath" width="100%" height="100%" />
          </div>
        </template>
      </split-pane>
    </div>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import SupplierDetail from './components/SupplierDetail'
import PartDocuments from './components/PartDocuments'
import attributeEdit from './components/attributeEditDialog'
import createSupplierPartDialog from './components/createSupplierPartDialog'
import splitPane from 'vue-splitpane'
import permission from '@/directive/permission/index.js'
import orderReqestDialog from '@/views/purchasing/components/orderRequestDialog'

export default {
  name: 'PartDetail',
  components: { splitPane, SupplierDetail, PartDocuments, attributeEdit, orderReqestDialog, createSupplierPartDialog },
  directives: { permission },
  props: {
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      partData: null,
      supplierPartData: null,
      documents: null,
      documentPath: null,
      stockData: null,
      productionPartData: null,
      availabilityData: null,
      orderRequests: null,
      purchaseOrder: null,
      purchaseOrderData: null,

      attributeEditVisible: false,
      createSupplierPartDialogVisible: false,
      orderReqestDialogVisible: false,
      orderReqestSupplierPartId: 0
    }
  },
  mounted() {
    this.getPartData()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.partData.ManufacturerPartNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      document.title = `${this.partData.ManufacturerName} - ${this.partData.ManufacturerPartNumber}`
    },
    getPartData() {
      requestBN({
        url: '/part/item',
        methood: 'get',
        params: { PartId: this.$route.params.partId }
      }).then(response => {
        this.partData = response.data[0]

        this.partData = response.data[0]

        this.partData.PartData.forEach(element => {
          const valArr = { Minimum: null, Maximum: null, Typical: null }

          if (typeof element.Value !== 'object') {
            valArr.Typical = element.Value
            element.Value = valArr
          }
        })

        this.documents = this.partData.Documents
        this.setTagsViewTitle()
        this.setPageTitle()
        this.getStockItems()
        this.getProductionPartData()
        this.getOrderRequests()
        this.getPurchasOrder()
      })
    },
    getStockItems() {
      requestBN({
        url: '/stock',
        methood: 'get',
        params: { ManufacturerPartId: this.partData.PartId }
      }).then(response => {
        this.stockData = response.data
      })
    },
    getProductionPartData() {
      requestBN({
        url: '/productionPart',
        methood: 'get',
        params: { ManufacturerPartId: this.partData.PartId }
      }).then(response => {
        this.productionPartData = response.data
      })
    },
    resize() { },
    handleChange(val) {
      if (val.includes('suppliers') && this.supplierData == null) {
        this.getSupplierPart()
      } else if (
        val.includes('availability') &&
        this.availabilityData == null
      ) {
        this.getAvailabilityData()
      }
    },
    getSupplierPart() {
      requestBN({
        url: '/supplier/supplierPart',
        methood: 'get',
        params: { ManufacturerPartId: this.partData.PartId }
      }).then(response => {
        this.supplierPartData = response.data
      })
    },
    getAvailabilityData() {
      requestBN({
        url: '/part/availability',
        methood: 'get',
        params: { ManufacturerPartId: this.partData.PartId }
      }).then(response => {
        this.availabilityData = response.data
      })
    },
    getOrderRequests() {
      requestBN({
        url: '/purchasing/orderRequest',
        methood: 'get',
        params: { ManufacturerPartId: this.partData.PartId }
      }).then(response => {
        this.orderRequests = response.data
      })
    },
    getPurchasOrder() {
      requestBN({
        url: '/purchasing/partPurchase',
        methood: 'get',
        params: { ManufacturerPartId: this.partData.PartId }
      }).then(response => {
        this.purchaseOrder = response.data
        this.purchaseOrderData = this.purchaseOrder.PurchaseOrderData
      })
    }
  }
}
</script>

<style scoped>
button {
  margin-right: 20px;
}

table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td,
th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
thead {
  font-weight: bold;
}

.center {
  text-align: center;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
img {
  width: 200px;
  float: left;
  padding-right: 20px;
}

.components-container {
  position: relative;
  height: 100vh;
}

.left-container {
  height: 100vh;
}

.right-container {
  height: 100vh;
}
</style>
