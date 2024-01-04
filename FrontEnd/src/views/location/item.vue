<template>
  <div class="app-container">
    <h1>{{ itemList.Name }}  - {{ LocationBarcode }}</h1>
    <el-divider />

    <p><b>Name:</b> {{ itemList.Name }}</p>
    <p><b>Title:</b> {{ itemList.Title }}</p>
    <p><b>Description:</b> {{ itemList.Description }}</p>
    <p><b>Movable:</b> {{ itemList.Movable }}</p>
    <p><b>Virtual:</b> {{ itemList.Virtual }}</p>
    <p><b>ESD:</b> {{ itemList.ESD }}</p>

    <template v-if="checkPermission(['location.edit'])">
      <el-button
        size="mini"
        type="primary"
        icon="el-icon-edit"
        circle
        style="margin-top: 00px; margin-bottom: 00px"
        @click="showEditDialog()"
      />
    </template>

    <el-divider />
    <h2>Display</h2>
    <p><b>Name:</b> {{ itemList.DisplayName }}</p>
    <p><b>Location:</b> {{ itemList.DisplayLocation }}</p>
    <p><b>Path:</b> {{ itemList.DisplayPath }}</p>
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

  </div>
</template>

<script>
import checkPermission from '@/utils/permission'

import editDialog from './components/editDialog'

import Location from '@/api/location'
const location = new Location()

export default {
  name: 'LocationItem',
  components: { editDialog },
  data() {
    return {
      loading: true,
      itemList: [],
      LocationBarcode: '',
      editDialogVisible: false
    }
  },
  mounted() {
    this.LocationBarcode = this.$route.params.LocationBarcode
    this.getLocationItems()
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
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
      this.inputItemNr = null
    },
    update() {
      this.getLocationItems()
    },
    showEditDialog() {
      this.editDialogVisible = true
    }
  }
}
</script>
