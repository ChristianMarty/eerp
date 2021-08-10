<template>
  <div class="app-container">
    <h1>
      {{ partData.ManufacturerName }}
      {{ partData.ManufacturerPartNumber }}
    </h1>
    <el-divider />
    <div class="components-container">
      <split-pane split="vertical" @resize="resize">
        <template slot="paneL">
          <el-container>
            <el-aside width="100%" style="background-color:white">
              <div class="left-container">
                <p><b>Manufacturer: </b>{{ partData.ManufacturerName }}</p>
                <p><b>Part Number: </b>{{ partData.ManufacturerPartNumber }}</p>
                <p><b>Category: </b>{{ partData.PartClassName }}</p>
                <p><b>Package: </b>{{ partData.Package }}</p>
                <p><b>Lifecycle Status: </b>{{ partData.Status }}</p>
                <p><b>Total Stock Quantity: </b>{{ partData.StockQuantity }}</p>

                <el-collapse @change="handleChange">
                  <el-collapse-item
                    name="elChar"
                  >
                    <template slot="title">
                      <b>Electrical Characteristics</b>
                    </template>
                    <el-table
                      :data="partData.PartData"
                      :default-sort="{ prop: 'Package', order: 'descending' }"
                      style="width: 100%"
                    >
                      <el-table-column prop="Name" />
                      <el-table-column prop="Value" label="Value" />
                      <el-table-column prop="Unit" label="Unit" />
                    </el-table>
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
                    <el-table
                      :data="supplierPartData"
                      style="width: 100%; margin-top:10px"
                    >
                      <el-table-column
                        prop="Name"
                        label="Name"
                        sortable
                      />
                      <el-table-column
                        prop="SupplierPartNumber"
                        label="Part Number"
                        sortable
                      />
                    </el-table>
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
                        <el-table-column
                          prop="Name"
                          label="Distributor"
                          width="150"
                        />
                        <el-table-column
                          prop="SKU"
                          label="SKU"
                        >
                          <template slot-scope="{ row }">
                            <a :href="row.URL" target="blank">
                              {{ row.SKU }}
                            </a>
                          </template>
                        </el-table-column>
                        <el-table-column
                          prop="Stock"
                          label="Stock"
                          width="80"
                        />
                        <el-table-column
                          prop="MinimumOrderQuantity"
                          label="MOQ"
                          width="80"
                        />
                        <el-table-column
                          prop="LeadTime"
                          label="LeadTime"
                          width="120"
                        />
                      </el-table>
                      <p><b>Timestamp:</b> {{ availabilityData.Timestamp }}, Data provided by Octopart</p>
                    </template>
                  </el-collapse-item>
                  <el-collapse-item
                    name="productionPart"
                  >
                    <template slot="title">
                      <b>Production Parts</b>
                    </template>
                    <el-table :data="productionPartData" style="width: 100%">
                      <el-table-column
                        prop="PartNo"
                        label="Part No"
                        sortable
                        width="100"
                      >
                        <template slot-scope="{ row }">
                          <router-link
                            :to="'/prodParts/prodPartView/' + row.PartNo"
                            class="link-type"
                          >
                            <span>{{ row.PartNo }}</span>
                          </router-link>
                        </template>
                      </el-table-column>
                      <el-table-column
                        prop="Description"
                        label="Description"
                        sortable
                      />
                    </el-table>
                  </el-collapse-item>
                  <el-collapse-item name="stock">
                    <template slot="title">
                      <b>Stock</b>
                    </template>
                    <el-table :data="stockData" style="width: 100%">
                      <el-table-column prop="StockNo" label="Stock No" sortable>
                        <template slot-scope="{ row }">
                          <router-link
                            :to="'/stock/item/' + row.StockNo"
                            class="link-type"
                          >
                            <span>{{ row.StockNo }}</span>
                          </router-link>
                        </template>
                      </el-table-column>

                      <el-table-column prop="Date" label="Date" sortable />
                      <el-table-column
                        prop="Quantity"
                        label="Quantity"
                        sortable
                      />
                      <el-table-column
                        prop="Location"
                        label="Location"
                        sortable
                      />
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
import splitPane from 'vue-splitpane'

export default {
  name: 'PartDetail',
  components: { splitPane, SupplierDetail, PartDocuments },
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
      availabilityData: null
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
    getPartData() {
      requestBN({
        url: '/part/item',
        methood: 'get',
        params: { PartId: this.$route.params.partId }
      }).then(response => {
        this.partData = response.data[0]
        this.documents = this.partData.Documents
        this.setTagsViewTitle()
        this.setPageTitle()
        this.getStockItems()
        this.getProductionPartData()
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
    resize() {},
    handleChange(val) {
      if (val.includes('suppliers') && this.supplierData == null) this.getSupplierPart()
      else if (val.includes('availability') && this.availabilityData == null) this.getAvailabilityData()
    },
    getSupplierPart() {
      requestBN({
        url: '/part/supplierPart',
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
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.partData.ManufacturerPartNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      const title = 'Part View'
      document.title = `${title} - ${this.partData.PartNo}`
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
