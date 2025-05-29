<template>
  <div class="app-container">
    <h1>{{ itemList.Name }}  - {{ itemList.ItemCode }}</h1>
    <el-divider />

    <p><b>Name:</b> {{ itemList.Name }}</p>
    <p><b>Title:</b> {{ itemList.Title }}</p>
    <p><b>Description:</b> {{ itemList.Description }}</p>
    <p><b>Movable:</b> {{ itemList.Movable }}</p>
    <p><b>Virtual:</b> {{ itemList.Virtual }}</p>
    <p><b>ESD:</b> {{ itemList.ESD }}</p>

    <el-button
      v-permission="['location.edit']"
      size="mini"
      type="primary"
      icon="el-icon-edit"
      circle
      style="margin-top: 00px; margin-bottom: 00px"
      @click="showEditDialog()"
    />
    <el-divider />

    <h2>Location</h2>
    <p><b>Name:</b> {{ itemList.DisplayName }}</p>
    <p><b>Location:</b> {{ itemList.DisplayLocation }}</p>
    <p><b>Path:</b> {{ itemList.DisplayPath }}</p>

    <el-button
      v-permission="['location.transfer']"
      type="primary"
      @click="locationTransferDialogVisible = true"
    >Location Transfer
    </el-button>
    <el-divider />

    <h2>Relationships</h2>
    <h3>Parent</h3>
    <p><router-link
         :to="'/location/item/' + itemList.Parent.LocationBarcode"
         class="link-type"
       >
         <span>{{ itemList.Parent.LocationBarcode }}</span>
       </router-link>
      <span> - {{ itemList.Parent.Name }}</span></p>
    <p>{{ itemList.Parent.Description }}</p>
    <h3>Children</h3>
    <el-table
      v-loading="loading"
      element-loading-text="Loading location data"
      :data="itemList.Children"
      border
      style="width: 100%"
    >
      <el-table-column prop="Item" label="Item Nr." width="120" sortable>
        <template slot-scope="{ row }">
          <router-link
            :to="'/location/item/' + row.LocationBarcode"
            class="link-type"
          >
            <span>{{ row.LocationBarcode }}</span>
          </router-link>
        </template>
      </el-table-column>
      <el-table-column prop="Name" label="Name" sortable />
      <el-table-column prop="Description" label="Description" sortable />
    </el-table>

    <h2>Items</h2>
    <p>{{ itemList.Items.length }} Items Found</p>

    <el-select v-model="selectedRendererId">
      <el-option v-for="item in rendererList" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
    </el-select>
    <el-select v-model="selectedPrinterId" style="margin-left: 20px">
      <el-option v-for="item in printerList" :key="Number(item.Id)" :label="item.Name" :value="Number(item.Id)" />
    </el-select>
    <el-button type="primary" style="margin-left: 20px" @click="print()">Print</el-button>
    <p />
    <el-table
      v-loading="loading"
      element-loading-text="Loading location data"
      :data="itemList.Items"
      border
      style="width: 100%"
    >
      <el-table-column prop="Item" label="Item Nr." width="120" sortable />
      <el-table-column prop="Category" label="Category" width="120" sortable />
      <el-table-column
        prop="Description"
        label="Description"
        sortable
      />
    </el-table>

    <editDialog
      :location-number="Number(itemList.LocationNumber)"
      :visible.sync="editDialogVisible"
      @change="update()"
    />

    <locationTransferDialog
      :barcode="itemList.ItemCode"
      :visible.sync="locationTransferDialogVisible"
      @change="getLocationItems()"
    />

  </div>
</template>

<script>
import checkPermission from '@/utils/permission'
import * as defaultSetting from '@/utils/defaultSetting'
import editDialog from './components/editDialog'

import Location from '@/api/location'
const location = new Location()

import Renderer from '@/api/renderer'
const renderer = new Renderer()

import Print from '@/api/print'
const print = new Print()

import Peripheral from '@/api/peripheral'
const peripheral = new Peripheral()

import locationTransferDialog from '@/components/Location/locationTransferDialog'

export default {
  name: 'LocationItem',
  components: { editDialog, locationTransferDialog },
  data() {
    return {
      loading: true,
      itemList: [],
      LocationBarcode: '',
      editDialogVisible: false,
      locationTransferDialogVisible: false,

      printerList: [],
      selectedPrinterId: 0,

      rendererList: [],
      selectedRendererId: 0
    }
  },
  async mounted() {
    this.LocationBarcode = this.$route.params.LocationBarcode
    this.getLocationItems()
    this.setTitle()
    this.printerList = await peripheral.list(peripheral.Type.Printer)
    this.rendererList = await renderer.list(true, renderer.Dataset.LocationInventoryList)

    this.selectedPrinterId = defaultSetting.defaultSetting().Location.Renderer.InventoryList.PeripheralId
    this.selectedRendererId = defaultSetting.defaultSetting().Location.Renderer.InventoryList.RendererId
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
        title: this.LocationBarcode
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
      document.title = this.LocationBarcode
    },
    getLocationItems() {
      this.loading = true
      location.item.get(this.LocationBarcode).then(response => {
        this.itemList = response
        this.loading = false
      })
      this.inputItemNr = null
    },
    update() {
      this.getLocationItems()
    },
    showEditDialog() {
      this.editDialogVisible = true
    },
    print() {
      print.print(this.selectedRendererId, this.selectedPrinterId, [this.$route.params.LocationBarcode]).then(response => {
      })
    }
  }
}
</script>
