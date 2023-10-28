<template>
  <div
    v-loading="loading"
    class="app-container"
    element-loading-text="Loading Document"
  >
    <h1>{{ documentData.Barcode }}</h1>

    <table>
      <tr>
        <th style="text-align: left;">Description</th>
        <td>{{ documentData.Description }}</td>
      </tr>
      <tr>
        <th style="text-align: left;">Note</th>
        <td>{{ documentData.Note }}</td>
      </tr>
      <tr>
        <th style="text-align: left;">Type</th>
        <td>{{ documentData.Type }}</td>
      </tr>
      <tr>
        <th style="text-align: left;">Link Type</th>
        <td>{{ documentData.LinkType }}</td>
      </tr>
      <tr>
        <th style="text-align: left;">Creation Date</th>
        <td>{{ documentData.CreationDate }}</td>
      </tr>
      <tr>
        <th style="text-align: left;">Hash</th>
        <td>{{ documentData.Hash }}</td>
      </tr>
    </table>

    <a :href="documentData.Path" target="blank">
      <el-button icon="el-icon-document" style="margin: 50px 0 20px 0">
        Open in new tab
      </el-button>
    </a>

    <h2>Citations</h2>
    <template>
      <el-table :data="documentData.Citations" style="width: 100%">
        <el-table-column
          prop="Category"
          label="Category"
          width="250"
          sortable
        />

        <el-table-column prop="Barcode" label="Barcode" width="120" sortable>
          <template slot-scope="{ row }">
            <router-link :to="getLinkToPage(row)" class="link-type">
              <span> {{ row.Barcode }}</span>
            </router-link>

          </template>
        </el-table-column>

        <el-table-column
          prop="Description"
          label="Description"
          sortable
        />
      </el-table>
    </template>

  </div>
</template>

<script>
import checkPermission from '@/utils/permission'

import Document from '@/api/document'
const document = new Document()

export default {
  name: 'DocumentView',
  components: {},
  props: {},
  data() {
    return {
      loading: true,
      documentData: {}
    }
  },
  mounted() {
    document.item(this.$route.params.DocumentNumber).then(response => {
      this.documentData = response
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
      const title = 'Part View'
      document.title = `${title} - ${this.documentData.Barcode}`

      const route = Object.assign({}, this.tempRoute, {
        title: `${this.documentData.Barcode}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    getLinkToPage(data) {
      let path = ''
      switch (data.Category) {
        case 'Inventory' : path = '/inventory/inventoryView/'; break

        case 'Inventory History' : path = '/inventory/inventoryView/'; break

        case 'Manufacturer Part Series' : path = '/manufacturerPart/series/item/'; break

        case 'Purchase Order' : path = '/purchasing/edit/'; break

        case 'Shipment' : path = '404'; break
      }

      return path + data.Barcode
    }

  }
}
</script>

