<template>
  <div class="app-container">
    <h1>{{ assemblyUnitData.AssemblyUnitBarcode }}</h1>
    <p><b>Assembly: </b>
      <router-link :to="'/assembly/item/' + assemblyUnitData.AssemblyBarcode" class="link-type">
        {{ assemblyUnitData.AssemblyBarcode }}
      </router-link>
    </p>
    <p><b>Serial Number: </b>{{ assemblyUnitData.SerialNumber }}</p>
    <p><b>Location: </b>{{ assemblyUnitData.LocationName }}</p>
    <p><b>Shipping Prohibited: </b>{{ assemblyUnitData.ShippingProhibited }}</p>
    <p><b>Shipping Clearance: </b>{{ assemblyUnitData.ShippingClearance }}</p>
    <p v-if="assemblyUnitData.ShippingProhibited" class="shippingProhibitedWarning">! DO NOT SEND THIS UNIT TO THE CUSTOMER !</p>
    <p v-if="assemblyUnitData.ShippingClearance" class="shippingClearance">This unit is ready for shipment.</p>

    <el-button v-permission="['location.transfer']" type="primary" @click="showLocationTransferDialog()">Location Transfer</el-button>
    <el-divider />

    <h2>History</h2>
    <el-button
      v-permission="['assembly.unit.history.add']"
      type="primary"
      icon="el-icon-plus"
      circle
      style="margin-top: 20px; margin-bottom: 20px"
      @click="showEditHistoryDialog(null)"
    />
    <el-timeline>
      <el-timeline-item
        v-for="(line, index) in assemblyUnitData.History"
        :key="index"
        :color="line.color"
        :timestamp="line.Date+' - '+ line.AssemblyUnitHistoryBarcode+' - '+line.Type"
        placement="top"
      >
        <el-card>
          <b>{{ line.Title }}</b>
          <p>{{ line.Description }}</p>
          <el-button @click.native="showHistoryDialog(line.AssemblyUnitHistoryBarcode)">Show Data</el-button>
          <el-button v-if="line.EditToken" v-permission="['assembly.unit.history.edit']" type="primary" @click.native=" showEditHistoryDialog(line.AssemblyUnitHistoryBarcode)">Edit</el-button>
          <p v-if="line.ShippingClearance" class="shippingClearance">Ready for shipment</p>
          <p v-if="line.ShippingProhibited" class="shippingProhibitedWarning">Shipping Prohibited</p>
        </el-card>
      </el-timeline-item>
    </el-timeline>

    <el-dialog :title="'Edit History Item'" :visible.sync="editHistoryVisible">
      <el-form label-width="170px">
        <el-form-item label="Title">
          <el-input v-model="editHistoryData.Title" />
        </el-form-item>
        <el-form-item label="Description">
          <el-input v-model="editHistoryData.Description" type="textarea" />
        </el-form-item>
        <el-form-item label="Type:">
          <el-select v-model="editHistoryData.Type" filterable>
            <el-option
              v-for="item in historyTypeOptions"
              :key="item"
              :label="item"
              :value="item"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Shipping Clearance">
          <el-checkbox v-model="editHistoryData.ShippingClearance" :disabled="assemblyUnitData.ShippingProhibited" />
        </el-form-item>
        <el-form-item label="Shipping Prohibited">
          <el-checkbox v-model="editHistoryData.ShippingProhibited" />
        </el-form-item>
        <el-form-item label="Data (JSON)">
          <el-input v-model="editHistoryData.Data" type="textarea" />
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="editHistoryItem();">Save</el-button>
        <el-button @click="editHistoryVisible = false">Cancel</el-button>
      </span>
    </el-dialog>

    <assemblyDataDialog :assembly-unit-history-number="assemblyUnitHistoryNumber" :visible.sync="assemblyDataDialogVisible" />
    <locationTransferDialog :barcode="assemblyUnitData.AssemblyUnitBarcode" :visible.sync="locationTransferDialogVisible" @change="getAssemblyItem()" />

  </div>
</template>

<script>
import permission from '@/directive/permission/index.js'
import assemblyDataDialog from './components/dataDialog'
import locationTransferDialog from '@/components/Location/locationTransferDialog'

import Assembly from '@/api/assembly'
const assembly = new Assembly()

export default {
  name: 'AssemblyView',
  components: { assemblyDataDialog, locationTransferDialog },
  directives: { permission },
  data() {
    return {
      assemblyUnitData: {},
      assemblyDataDialogVisible: false,

      editHistoryData: {},
      historyItemData: {},
      editHistoryVisible: false,
      assemblyUnitHistoryNumber: 0,
      historyTypeOptions: [],

      locationTransferDialogVisible: false
    }
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  async mounted() {
    this.getAssemblyItem()
    this.setTitle()
    this.historyTypeOptions = await assembly.unit.history.types()
  },
  methods: {
    setTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.$route.params.AssemblyUnitNumber}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = `${this.$route.params.AssemblyUnitNumber}`
    },
    showLocationTransferDialog() {
      this.locationTransferDialogVisible = true
    },
    showEditHistoryDialog(id) {
      if (id === null) {
        this.editHistoryData = {}
      } else {
        this.getHistoryData(id)
      }

      this.editHistoryVisible = true
    },
    showHistoryDialog(id) {
      this.assemblyUnitHistoryNumber = id
      this.assemblyDataDialogVisible = true
    },
    editHistoryItem() {
      if (this.editHistoryData.EditToken == null) {
        this.addHistoryItem()
      } else {
        this.updateHistoryItem()
      }
    },
    addHistoryItem() {
      const historyCreateParameters = Object.assign({}, assembly.unit.history.historyCreateParameters)
      historyCreateParameters.AssemblyUnitNumber = this.assemblyUnitData.AssemblyUnitNumber
      historyCreateParameters.Title = this.editHistoryData.Title
      historyCreateParameters.Description = this.editHistoryData.Description
      historyCreateParameters.Type = this.editHistoryData.Type
      historyCreateParameters.ShippingClearance = this.editHistoryData.ShippingClearance
      historyCreateParameters.ShippingProhibited = this.editHistoryData.ShippingProhibited
      historyCreateParameters.Data = this.editHistoryData.Data

      assembly.unit.history.create(historyCreateParameters).then(response => {
        this.editHistoryVisible = false
        this.editHistoryData = {}
        this.getAssemblyItem()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 1500,
          type: 'error'
        })
      })
    },
    updateHistoryItem() {
      const historyUpdateParameters = Object.assign({}, assembly.unit.history.historyUpdateParameters)
      historyUpdateParameters.EditToken = this.editHistoryData.EditToken
      historyUpdateParameters.Title = this.editHistoryData.Title
      historyUpdateParameters.Description = this.editHistoryData.Description
      historyUpdateParameters.Type = this.editHistoryData.Type
      historyUpdateParameters.ShippingClearance = this.editHistoryData.ShippingClearance
      historyUpdateParameters.ShippingProhibited = this.editHistoryData.ShippingProhibited
      historyUpdateParameters.Data = this.editHistoryData.Data

      assembly.unit.history.update(historyUpdateParameters).then(response => {
        this.editHistoryVisible = false
        this.editHistoryData = {}
        this.getAssemblyItem()
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 1500,
          type: 'error'
        })
      })
    },
    getHistoryData(AssemblyUnitHistoryNumber) {
      assembly.unit.history.item(AssemblyUnitHistoryNumber).then(response => {
        this.editHistoryData = response
        var data = this.editHistoryData.Data
        if (data === null) this.editHistoryData.Data = ''
        else this.editHistoryData.Data = JSON.stringify(data)
        this.editHistoryToken = this.editHistoryData.EditToken
      })
    },
    getAssemblyItem() {
      assembly.unit.item(this.$route.params.AssemblyUnitNumber).then(response => {
        this.assemblyUnitData = response
        // 'Unknown','Note','Production','Inspection Fail','Inspection Pass','Repair','Test Fail','Test Pass'
        this.assemblyUnitData.History.forEach(element => {
          switch (element.Type) {
            case 'Test Pass':
            case 'Inspection Pass':
              element.color = '#67C23A' // Green
              break
            case 'Test Fail':
            case 'Inspection Fail':
              element.color = '#F56C6C' // Red
              break
            case 'Repair':
              element.color = '#E6A23C' // Orange
              break
            case 'Production':
            case 'Modification':
              element.color = '#409EFF' // Blue
              break
            case 'Note':
              element.color = '#909399' // Gray
              break
          }
        })
      })
    }
  }

}
</script>

<style scoped>

.shippingProhibitedWarning {
  color: red;
  font-size: 25px;
}

.shippingClearance {
  color:green;
}

</style>
