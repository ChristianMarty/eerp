<template>
  <div class="app-container">
    <template>
      <el-table
        :data="locations"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="Id"
        border
        :tree-props="{ children: 'Children' }"
      >
        <el-table-column prop="Name" label="Name" />
        <el-table-column prop="LocNr" label="Location Nr">
          <template slot-scope="{ row }">
            <router-link
              :to="'/location/item/' + row.LocNr"
              class="link-type"
            >
              <span>{{ row.LocNr }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="Attributes.EsdSave" label="ESD Save">
          <template slot-scope="scope">
            <span v-if="scope.row.Attributes.EsdSave == true">
              Yes
            </span>
            <span v-if="scope.row.Attributes.EsdSave == false">
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
      locations: []
    }
  },
  async mounted() {
    this.locations = await location.search()
  },
  methods: {
  }
}
</script>
