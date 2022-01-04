<template>
  <div class="app-container">
    <h1>
      {{ inventoryData.InvNo }} - {{ inventoryData.Titel }},
      {{ inventoryData.Manufacturer }}
      {{ inventoryData.Type }}
    </h1>
    <el-divider />

    <el-container>
      <el-aside>
        <el-image
          style="width: 250px;"
          :src="inventoryData.PicturePath"
          :fit="fit"
        />
      </el-aside>
      <el-main>
        <p><b>Location: </b>{{ inventoryData.LocationName }}</p>
        <p><b>Home Location: </b>{{ inventoryData.HomeLocationName }}</p>
        <p><b>Purchase Date: </b>{{ inventoryData.PurchaseDate }}</p>
        <p><b>Purchase Price: </b>{{ inventoryData.PurchasePrice }}</p>
        <p><b>Supplier: </b>{{ inventoryData.SupplierName }}</p>
        <p><b>Serial Number: </b>{{ inventoryData.SerialNumber }}</p>
        <p><b>MAC Address Wired: </b>{{ inventoryData.MacAddressWired }}</p>
        <p>
          <b>MAC Address Wireless: </b>{{ inventoryData.MacAddressWireless }}
        </p>
        <p><b>Status: </b>{{ inventoryData.Status }}</p>
      </el-main>
    </el-container>
    <el-divider />
    <h2>Description</h2>
    <pre>
      <div class="Description-content" v-html="inventoryData.Description" />
    </pre>
    <el-divider />
    <h2>Documents</h2>
    <el-table :data="inventoryData.Documents" style="width: 100%">
      <el-table-column prop="Description" label="Description" width="400" />
      <el-table-column prop="Type" label="Type" width="100" />
      <el-table-column prop="Document" label="Document">
        <template slot-scope="{ row }">
          <a :href="row.Path" target="blank">
            <el-button icon="el-icon-document">
              Open in new tab
            </el-button>
          </a>
        </template>
      </el-table-column>
    </el-table>
    <h2>Note</h2>
    <p>{{ inventoryData.Note }}</p>
    <el-divider />

    <h2>History</h2>
    <el-timeline reverse="true">
      <el-timeline-item
        v-for="(line, index) in inventoryData.History"
        :key="index"
        :color="line.color"
        :timestamp="line.Date"
        placement="top"
      >
        <el-card>
          <b>{{ line.Type }}</b>
          <p>{{ line.Description }}</p>
          <p
            v-for="(doc, index2) in line.Documents"
            :key="index2"
          >
            <a :href="doc.Path" target="blank">
              <el-button icon="el-icon-document">
                {{ doc.Description }}
              </el-button>
            </a>
          </p>
          <p v-if="line.NextDate">Next {{ line.Type }}: {{ line.NextDate }}</p>
        </el-card>
      </el-timeline-item>
    </el-timeline>

    <el-divider />
    <el-button v-if="checkPermission(['inventory.print'])" type="primary" @click="addPrint">Print</el-button>
    <el-button v-if="checkPermission(['inventory.create'])" type="primary" @click="copy">Create Copy</el-button>
  </div>
</template>

<script>
import requestBN from '@/utils/requestBN'
import Cookies from 'js-cookie'
import checkPermission from '@/utils/permission'

export default {
  name: 'InventoryView',
  components: {},
  data() {
    return {
      inventoryData: null
    }
  },
  mounted() {
    this.getInventoryData()
  },
  created() {
    // Why need to make a copy of this.$route here?
    // Because if you enter this page and quickly switch tag, may be in the execution of the setTagsViewTitle function, this.$route is no longer pointing to the current page
    // https://github.com/PanJiaChen/vue-element-admin/issues/1221
    this.tempRoute = Object.assign({}, this.$route)
  },
  methods: {
    checkPermission,
    getInventoryData() {
      requestBN({
        url: '/inventory/item',
        methood: 'get',
        params: { InvNo: this.$route.params.invNo }
      }).then(response => {
        this.inventoryData = response.data
        this.setTagsViewTitle()
        this.setPageTitle()
      })
    },
    addPrint() {
      var cookieList = []
      try {
        var cookiesText = Cookies.get('invNo')
        cookieList = JSON.parse(cookiesText)
      } catch (e) {
        cookieList = []
      }

      var invNoList = []
      invNoList = invNoList.concat(cookieList)

      invNoList.push(this.inventoryData.InvNo)
      Cookies.set('invNo', invNoList)

      this.$message({
        showClose: true,
        message: this.inventoryData.InvNo + ' Added to Printer Queue',
        type: 'success'
      })
    },
    copy() {
      this.$router.push(
        '/inventory/inventoryCreate/' + this.inventoryData.InvNo
      )
    }
  },
  setTagsViewTitle() {
    const route = Object.assign({}, this.tempRoute, {
      title: `${this.inventoryData.InvNo}`
    })
    this.$store.dispatch('tagsView/updateVisitedView', route)
  },
  setPageTitle() {
    const title = 'Part View'
    document.title = `${title} - ${this.inventoryData.InvNo}`
  }
}
</script>

<style scoped>
.el-aside {
  background-color: #ffffff;
  color: #333;
  text-align: center;
}

.el-main {
  color: #333;
}
</style>
