<template>
  <div class="app-container">
    <h1>{{ partData.ManufacturerName }} - {{ partData.DisplayPartNumber }}</h1>
    <!--<h2>{{ partData.Description }} </h2>-->
    <p><b>Series:</b>
      <router-link :to="'/manufacturerPart/series/item/' + partData.SeriesId" class="link-type">
        <span> {{ partData.SeriesTitle }}</span> - {{ partData.SeriesDescription }}
      </router-link>
    </p>
    <p><b>Package: </b>{{ partData.Package }}</p>
    <el-divider />

    <!--<el-tabs type="card">
      <el-tab-pane label="Engineering">TBD Engineering</el-tab-pane>
      <el-tab-pane label="Purchasing">TBD Purchasing</el-tab-pane>
      <el-tab-pane label="Production">TBD Production</el-tab-pane>
      <el-tab-pane label="Stock">TBD Stock</el-tab-pane>
    </el-tabs>-->

    <h3>Part Characteristics</h3>
    <template v-permission="['manufacturerPart.edit']">
      <el-button
        type="primary"
        icon="el-icon-edit"
        circle
        @click="showPartCharacteristicsDialog()"
      />
    </template>
    <p><b>Class: </b>{{ partData.PartClassPath }}</p>
    <el-table
      :data="partData.Attribute"
      style="width: 100%"
    >
      <el-table-column prop="Name" width="200px" />
      <el-table-column prop="Value.Minimum" label="Minimum" align="center" />
      <el-table-column prop="Value.Typical" label="Typical" align="center" />
      <el-table-column prop="Value.Maximum" label="Maximum" align="center" />
      <el-table-column prop="Symbol" label="Unit" />
    </el-table>

    <el-divider />
    <h3>Part Number</h3>
    <p><b>Part Number Template:</b> {{ partData.PartNumber }}</p>
    <p><b>Part Number Description:</b> {{ partData.PartNumberDescription }}</p>
    <el-table
      :data="partData.PartNumberItem"
      style="width: 100%"
      row-key="key"
      border
      :tree-props="{ children: 'ProductionPart' }"
    >
      <el-table-column prop="ManufacturerPartNumber" label="Number">
        <template slot-scope="{ row }">
          <router-link :to="'/manufacturerPart/partNumber/item/' + row.ManufacturerPartNumberId" class="link-type">
            <span>{{ row.ManufacturerPartNumber }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="ProductionPartItemCode" label="Production Part Number">
        <template slot-scope="{ row }">
          <router-link
            :to="'/productionPart/item/' + row.ProductionPartItemCode"
            class="link-type"
          >
            <span>{{ row.ProductionPartItemCode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="ManufacturerPartNumberDescription" label="Description" />
    </el-table>

    <h3>Documents</h3>
    <editDocumentsList
      attach="ManufacturerPartItemDocument"
      :barcode="partData.PartId"
      @change="getManufacturerPartItem()"
    />
    <documentsList :documents="partData.Documents" />

    <partCharacteristicsDialog
      :part-id="partData.PartId"
      :visible.sync="partCharacteristicsDialogVisible"
      @change="getManufacturerPartItem()"
    />

  </div>
</template>

<script>

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

import documentsList from '@/views/document/components/documentsList'
import editDocumentsList from '@/views/document/components/editDocumentsList'

import partCharacteristicsDialog from './components/partCharacteristicsDialog'

export default {
  name: 'PartSeriesBrowser',
  components: { partCharacteristicsDialog, documentsList, editDocumentsList },
  data() {
    return {
      partCharacteristicsDialogVisible: false,
      loading: true,
      partData: {}
    }
  },
  mounted() {
    this.getManufacturerPartItem()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    setTitle() {
      const title = this.partData.ManufacturerName + ' - ' + this.partData.DisplayPartNumber
      const route = Object.assign({}, this.tempRoute, {
        title: title
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = title
    },
    showPartCharacteristicsDialog() {
      this.partCharacteristicsDialogVisible = true
    },
    getManufacturerPartItem() {
      manufacturerPart.item.get(this.$route.params.ManufacturerPartItemId).then(response => {
        // Add key needed for table rendering
        let key = 1
        response.PartNumberItem.forEach(element => {
          element.key = key
          key++
          if (element.ProductionPart.length > 1) {
            element.ProductionPart.forEach(element2 => {
              element2.key = key
              key++
            })
          } else if (element.ProductionPart.length > 0) {
            const part = element.ProductionPart[0]
            element.ProductionPartItemCode = part.ProductionPartItemCode
            element.ManufacturerPartNumberDescription = part.ManufacturerPartNumberDescription
            element.ProductionPart = []
          }
        })

        this.partData = response
        this.loading = false
        this.partData.Attribute.forEach(element => {
          const valArr = { Minimum: null, Maximum: null, Typical: null }
          if (typeof element.Value !== 'object') {
            valArr.Typical = element.Value
            element.Value = valArr
          }
        })

        this.setTitle()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
    }
  }
}
</script>
