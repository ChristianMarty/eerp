<template>
  <div class="app-container">
    <h1>
      {{ inventoryData.InvNo }} - {{ inventoryData.Title }},
      {{ inventoryData.Manufacturer }}
      {{ inventoryData.Type }}
    </h1>
    <el-divider />

    <el-container>
      <el-aside>
        <el-image style="width: 250px;" :src="inventoryData.PicturePath" :fit="fit" />
      </el-aside>
      <el-main>
        <p>
          <b>Location:</b>
          {{ inventoryData.Location }}
        </p>
        <p>
          <b>Home Location:</b>
          {{ inventoryData.HomeLocation }}
        </p>
        <p>
          <b>MAC Address Wired:</b>
          {{ inventoryData.MacAddressWired }}
        </p>
        <p>
          <b>Status:</b>
          {{ inventoryData.Status }}
        </p>
      </el-main>
      <el-main>
        <p>
          <b>Location Path:</b>
          {{ inventoryData.LocationPath }}
        </p>
        <p>
          <b>Home Location Path:</b>
          {{ inventoryData.HomeLocationPath }}
        </p>
        <p>
          <b>MAC Address Wireless:</b>
          {{ inventoryData.MacAddressWireless }}
        </p>
        <p>
          <b>Serial Number:</b>
          {{ inventoryData.SerialNumber }}
        </p>
      </el-main>
    </el-container>
    <el-divider />

    <h2>Purchase Information</h2>

    <p>
      <b>PO No:</b>
      <router-link :to="'/purchasing/edit/' + inventoryData.PurchaseInformation.PoNo" class="link-type">
        <span>{{ inventoryData.PurchaseInformation.PoNo }}</span>
      </router-link>
    </p>
    <p>
      <b>Price:</b>
      {{ inventoryData.PurchaseInformation.Price }} {{ inventoryData.PurchaseInformation.Currency }}
    </p>
    <p>
      <b>Date:</b>
      {{ inventoryData.PurchaseInformation.PurchaseDate }}
    </p>

    <p>
      <b>Supplier:</b>
      <router-link :to="'/supplier/supplierView/' + inventoryData.PurchaseInformation.VendorId" class="link-type">
        <span>{{ inventoryData.PurchaseInformation.SupplierName }}</span>
      </router-link>
    </p>
    <p>
      <b>Part Number:</b>
      {{ inventoryData.PurchaseInformation.SupplierPartNumber }}
    </p>
    <p>
      <b>Order Reference:</b>
      {{ inventoryData.PurchaseInformation.OrderReference }}
    </p>
    <el-divider />
    <h2>Description</h2>
    <pre>
      <div class="Description-content" v-html="inventoryData.Description" />
    </pre>
    <el-divider />
    <h2>Note</h2>
    <pre>
      <div class="Note-content" v-html="inventoryData.Note" />
    </pre>
    <el-divider />
    <h2>Documents</h2>
    <documentsList :documents="inventoryData.Documents" />
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
          <p v-for="(doc, index2) in line.Documents" :key="index2">
            <a :href="doc.Path" target="blank">
              <el-button icon="el-icon-document">{{ doc.Description }}</el-button>
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
import documentsList from '@/views/documents/components/listDocuments'

export default {
  name: 'InventoryView',
  components: { documentsList },
  data() {
    return {
      inventoryData: null,
      purchaseInformation: null
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
    setTagsViewTitle() {
      const route = Object.assign({}, this.tempRoute, {
        title: `${this.inventoryData.InvNo}`
      })
      this.$store.dispatch('tagsView/updateVisitedView', route)
    },
    setPageTitle() {
      document.title = `${this.inventoryData.InvNo} - ${this.inventoryData.Title}`
    },
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
