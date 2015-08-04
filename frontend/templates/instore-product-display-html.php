<?php if( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly 
global $woocommerce;
?>

<div class="product_detail" style="display:none">
  <table>
    <tbody>
      <tr>
        <td class="title heading" colspan="3"></td>
        <td class="label" style="text-align:right;">Current Price:</td>
        <td class="price heading" style="text-align:center;"></td>
      </tr>
      <tr>
        <td class="instock" colspan="3"></td>
      </tr>
      <tr>
        <td class="label">SKU:</td>
        <td class="sku value"></td>
        <td></td>
        <td class="label">On Sale:</td>
        <td class="on_sale value"></td>
      </tr>
    
      <td class="label">Sale Price:
      
      <td class="sale_price value"></td>
      <td></td>
      <td class="label">Availibility:</td>
      <td class="stock_count value"></td>
    </tr>
    <tr>
      <td class="label">Regular price:</td>
      <td class="regular_price value"></td>
      <td></td>
      <td class="label">Product type:</td>
      <td class="product_type value"></td>
    </tr>
    <tr>
      <td class="label">Select Quantity</td>
      <td class="order_action value" colspan="2"><input type="number" class="product_quantity" style="width:30px;" min="1" max="" step="1" value="1" /></td>
      <td></td>
      <td class="order_action"><input type="button" class="order_item_button" id="179" value="Add To Order" /></td>
    </tr>
      </tbody>
    
  </table>
</div>
