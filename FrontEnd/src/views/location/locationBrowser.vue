<template>
  <div class="app-container">
    <template>
      <el-table
        :data="packages"
        style="width: 100%;"
        :cell-style="{ padding: '0', height: '20px' }"
        row-key="Id"
        border
        stripe
        :tree-props="{ children: 'Children' }"
      >
        >
        <el-table-column prop="Name" label="Name" />
        <el-table-column prop="LocNr" label="Location Nr">
          <template slot-scope="{ row }">
            <router-link
              :to="'/location/summary/' + row.LocNr"
              class="link-type"
            >
              <span>{{ row.LocNr }}</span>
            </router-link>
          </template>
        </el-table-column>
        <el-table-column prop="ESD" label="ESD Save">
          <template slot-scope="scope">
            <span v-if="scope.row.ESD == true">
              Yes
            </span>
            <span v-if="scope.row.ESD == false">
              No
            </span>
          </template>
        </el-table-column>
      </el-table>
    </template>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'

export default {
  name: 'LocationBrowser',
  components: {},
  data() {
    return {
      packages: null
    }
  },
  mounted() {
    this.getPackages()
  },
  methods: {
    getPackages() {
      requestBN({
        url: '/location',
        methood: 'get'
      }).then(response => {
        this.packages = response.data
      })
    }
  }
}
</script>
