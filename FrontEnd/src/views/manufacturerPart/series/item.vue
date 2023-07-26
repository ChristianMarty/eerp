<template>
  <div class="app-container">
    <h2>{{ data.ManufacturerName }} - {{ data.Title }}</h2>
    <p><b>{{ data.ClassName }}</b></p>
    <p>{{ data.Description }}</p>

    <h3>Manufacturer Part Parameter</h3>
    <p><b>Part Number Template:</b> {{ data.NumberTemplate }} </p>
    <template v-if="checkPermission(['manufacturerPartSeries.edit'])">
      <el-button
        size="mini"
        type="primary"
        icon="el-icon-edit"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showEditDialog()"
      />
    </template>
    <el-table
      v-loading="loading"
      element-loading-text="Loading Manufacturer Parts"
      :data="data.Parameter"
      border
      style="width: 100%"
      row-key="rowKey"
      :cell-style="{ padding: '0', height: '15px' }"
      :tree-props="{ children: 'Values' }"
    >
      <el-table-column label="Name" prop="Name" sortable />
      <el-table-column label="Value" prop="Value" sortable />
      <el-table-column label="Description" prop="Description" sortable />
      <el-table-column label="Type" prop="Type" sortable />
    </el-table>

    <h3>Items</h3>
    <el-table
      v-loading="loading"
      element-loading-text="Loading Manufacturer Parts"
      :data="data.Item"
      style="width: 100%;"
      :cell-style="{ padding: '0', height: '20px' }"
      row-key="rowKey"
      :tree-props="{ children: 'PartNumber' }"
      border
    >
      <el-table-column label="Number" prop="Number" sortable>
        <template slot-scope="{ row }">
          <template v-if="row.LineType === 'Part' ">
            <router-link :to="'/manufacturerPart/item/' + row.ItemId" class="link-type">
              <span> {{ row.Number }}</span>
            </router-link>
          </template>
          <template v-if="row.LineType === 'PartNumber' ">
            <router-link :to="'/manufacturerPart/partNumber/item/' + row.PartNumberId" class="link-type">
              <span> {{ row.Number }}</span>
            </router-link>
          </template>
        </template>
      </el-table-column>
      <el-table-column label="Description" prop="Description" sortable />
      <el-table-column label="Marking Code" prop="MarkingCode" sortable />
    </el-table>

    <h3>Documents</h3>
    <editDocumentsList
      attach="ManufacturerPartSeriesDocument"
      :barcode="data.ManufacturerPartSeriesId"
      @change="getManufacturerPartSeriesItem()"
    />
    <documentsList :documents="data.Documents" />

    <templateDialog :visible.sync="templateDialogVisible" :manufacturer-part-series-id="data.ManufacturerPartSeriesId" />

  </div>
</template>

<script>
import checkPermission from '@/utils/permission'

import documentsList from '@/views/document/components/documentsList'
import editDocumentsList from '@/views/document/components/editDocumentsList'
import templateDialog from './components/templateDialog'

import ManufacturerPart from '@/api/manufacturerPart'
const manufacturerPart = new ManufacturerPart()

export default {
  name: 'PartSeriesItem',
  components: { documentsList, editDocumentsList, templateDialog },
  data() {
    return {
      loading: true,

      data: {},

      templateDialogVisible: false
    }
  },
  mounted() {
    this.getManufacturerPartSeriesItem()
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
        title: this.data.ManufacturerName + ' - ' + this.data.Title
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = this.data.ManufacturerName + ' - ' + this.data.Title
    },
    showEditDialog() {
      this.templateDialogVisible = true
    },
    getManufacturerPartSeriesItem() {
      manufacturerPart.series.item(this.$route.params.ManufacturerPartSeriesId).then(response => {
        this.data = response

        this.data.Parameter = Object.values(this.data.Parameter)

        let rowKey = 1
        this.data.Parameter.forEach(element => {
          element.rowKey = String(rowKey)
          rowKey++
          if (element.Values.length === 0) {
            delete element.Values
          } else {
            let rowKey2 = 1
            element.Values.forEach(element2 => {
              element2.rowKey = element.rowKey + '.' + String(rowKey2)
              rowKey2++
            })
          }
        })

        rowKey = 1
        this.data.Item.forEach(element => {
          element.rowKey = String(rowKey)
          element.LineType = 'Part'
          rowKey++
          if (element.PartNumber.length === 0) {
            delete element.PartNumber
          } else {
            let rowKey2 = 1
            element.PartNumber.forEach(element2 => {
              element2.rowKey = element.rowKey + '.' + String(rowKey2)
              element2.LineType = 'PartNumber'
              rowKey2++
            })
          }
        })

        this.setTitle()

        this.loading = false
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
