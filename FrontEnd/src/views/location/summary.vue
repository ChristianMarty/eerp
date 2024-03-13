<template>
  <div class="app-container">
    <h1>Location Summary</h1>
    <el-divider />

    <p>
      <el-input
        ref="locNrInput"
        v-model="inputLocNr"
        placeholder="Please input"
        @keyup.enter.native="getSummary"
      >
        <el-button
          slot="append"
          icon="el-icon-search"
          @click="getSummary"
        />
      </el-input>
    </p>
    <p>
      <el-cascader-panel
        v-model="inputLocNr"
        :options="locations"
        :props="{
          emitPath: false,
          value: 'ItemCode',
          label: 'Name',
          children: 'Children',
          checkStrictly: true
        }"
        @change="getSummary"
      />
    </p>
    <p>
      <el-button type="danger" @click="resetForm">Clear</el-button>
    </p>

    <h2>{{ itemList.length }} Items Found</h2>

    <p>
      <el-table :data="itemList" border style="width: 100%">
        <el-table-column prop="Item" label="Item Nr." width="120" sortable />
        <el-table-column prop="Category" label="Category" width="120" sortable />
        <el-table-column
          prop="Description"
          label="Description"
          sortable
        />
      </el-table>
    </p>
  </div>
</template>

<script>
import Location from '@/api/location'
const location = new Location()

export default {
  name: 'LocationAssignment',
  data() {
    return {
      locations: Object.assign({}, location.searchReturn),
      inputLocNr: '',
      itemList: []
    }
  },
  async mounted() {
    this.locations = await location.search()

    if (this.$route.params.LocationNr != null) {
      if (this.$route.params.LocationNr !== ':LocationNr(.*)') {
        this.inputLocNr = this.$route.params.LocationNr
        this.getSummary()
      }
    }
  },
  methods: {
    resetForm() {
      this.inputLocNr = null
      this.itemList = []
    },
    getSummary() {
      location.summary(this.inputLocNr).then(response => {
        this.itemList = response
      }).catch(response => {
        this.$message({
          showClose: true,
          message: response,
          duration: 0,
          type: 'error'
        })
      })
      this.inputItemNr = null
    }
  }
}
</script>
