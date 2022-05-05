<template>
  <div class="order-total">
    <div style="display: flex; justify-content: flex-end; margin-top: 20px">
      <table style='border-collapse:collapse'>
        <tr><td style="text-align: right"><b>Total Net:</b></td><td style='width:100px; padding-left: 10px'>{{ calcNetTotal() }}</td></tr>
        <tr><td style="text-align: right"><b>Total Discount:</b></td><td style='width:100px; padding-left: 10px'>{{ calcDiscountTotal() }}</td></tr>
        <tr><td style="text-align: right"><b>Total VAT:</b></td><td style='width:100px; padding-left: 10px'>{{ calcVatTotal() }}</td></tr>
        <tr style="border-top: 1px solid black" ><td style="text-align: right"><b>Total:</b></td><td style='width:100px; padding-left: 10px'>{{ calcTotal() }}</td></tr>
      </table>
    </div>
  </div>
</template>

<script>

import requestBN from '@/utils/requestBN'

export default {
  name: 'OrderTotal',
  props: { lines: { type: Object, default: {} }, vat: { type: Object, default: {} } },
  data() {
    return {
    }
  },
  mounted() {
  },
  methods: {
    calcTotal(){
       let vat = this.calcVatTotal()
       let net = this.calcNetTotal()
       let dsc = this.calcDiscountTotal()

       return  Math.round(((net+dsc)+vat) * 10000) / 10000
    },
    calcVatTotal(){
       let total = 0
        this.lines.forEach(element => {
          let vat = this.vat.find(o => o.Id === Number(element.VatTaxId)).Value
          const line = ((element.QuantityOrderd * element.Price) * ((100-vat)/100)) * (7.7 / 100)
          total += line
        })
       return  Math.round(total * 10000) / 10000
    },
    calcDiscountTotal(){
       let total = 0
        this.lines.forEach(element => {
          const line = (element.QuantityOrderd * element.Price) * (element.Discount / 100)
          total += line
        })
       return  -1*Math.round(total * 10000) / 10000
    },
    calcNetTotal(){
        let total = 0
        this.lines.forEach(element => {
          const line = element.QuantityOrderd * element.Price
          total += line
        })
       return  Math.round(total * 10000) / 10000
    }
  }
}
</script>
