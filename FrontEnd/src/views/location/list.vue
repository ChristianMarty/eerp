<template>
  <div class="app-container">
    <template>
      <el-table
        v-loading="loading"
        element-loading-text="Loading location data"
        :data="locations"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="LocationNumber"
        border
        :tree-props="{ children: 'Children' }"
      >
        <el-table-column prop="ItemCode" label="Location Number" width="250px">
          <template slot-scope="{ row }">
            <router-link
              :to="'/location/item/' + row.ItemCode"
              class="link-type"
            >
              <span>{{ row.ItemCode }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Name" label="Name" width="200px" />
        <el-table-column prop="Title" label="Title" width="200px" />
        <el-table-column prop="Description" label="Description" />
        <el-table-column prop="Attribute.EsdSave" label="ESD Save">
          <template slot-scope="scope">
            <span v-if="scope.row.Attribute.EsdSave == true">
              Yes
            </span>
            <span v-if="scope.row.Attribute.EsdSave == false">
              No
            </span>
          </template>
        </el-table-column>
      </el-table>
    </template>
  </div>
</template>

<script>
import Location from '@/api/location'
const location = new Location()

export default {
  name: 'LocationBrowser',
  components: {},
  data() {
    return {
      loading: true,
      locations: []
    }
  },
  mounted() {
    this.loading = true
    location.search().then(response => {
      this.locations = response
      this.loading = false
    })
  },
  methods: {
  }
}
</script>
