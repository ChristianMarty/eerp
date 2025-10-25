<template>
  <div
    v-loading="loading"
    class="app-container"
    element-loading-text="Loading Document"
  >
    <h1>{{ documentData.ItemCode }} - {{ documentData.Name }}</h1>
    <table>
      <tr>
        <th style="text-align: left;">Created</th>
        <td>{{ documentData.CreatedBy.Initials }} / {{ documentData.CreationDate }}</td>
      </tr>
      <tr>
      </tr>
      <tr>
        <th style="text-align: left;">Category</th>
        <td>{{ documentData.Category }}</td>
      </tr>
    </table>

    <h3>Description</h3>
    <p>{{ documentData.Description }}</p>

    <h2>Revisions</h2>
    <el-table :data="documentData.Revision" style="width: 100%">
      <el-table-column
        prop="ItemCode"
        label="Item"
        width="120"
        sortable
      />
      <el-table-column label="Link" width="200">
        <template slot-scope="{ row }">
          <a :href="row.Path" target="blank">
            <el-button icon="el-icon-document">
              Open Document
            </el-button>
          </a>
        </template>
      </el-table-column>
      <el-table-column prop="Type" label="Type" width="80"/>
      <el-table-column prop="Description" label="Description" />
      <el-table-column prop="CreatedBy.Initials" label="Initials" width="80"/>
      <el-table-column prop="CreationDate" label="Creation Date" width="180"/>
      <el-table-column prop="Hash" label="Hash" width="280"/>
    </el-table>


    <h2>Citations</h2>
    <template>
      <el-table :data="documentData.Citation" style="width: 100%">
        <el-table-column
          prop="ItemCode"
          label="Item"
          width="120"
          sortable
        >
          <template slot-scope="{ row }">
            <router-link :to="getLinkToPage(row)" class="link-type">
              <span> {{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>

        <el-table-column
          prop="Category"
          label="Category"
          width="200"
          sortable
        />

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
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.documentData.ItemCode}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    getLinkToPage(data) {
      let path = ''
      switch (data.Category) {
        case 'Inventory' : path = '/inventory/item/'; break

        case 'Inventory History' : path = '/inventory/item/'; break

        case 'Manufacturer Part Series' : path = '/manufacturerPart/series/item/'; break

        case 'Purchase Order' : path = '/purchasing/edit/'; break

        case 'Shipment' : path = '404'; break
      }

      return path + data.ItemCode
    }

  }
}
</script>

