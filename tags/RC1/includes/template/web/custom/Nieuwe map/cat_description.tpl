{*
 * %%%copyright%%%
 *
 * FusionTicket - ticket reservation system
 * Copyright (C) 2007-2008 Christopher Jenkins. All rights reserved.
 *
 * Original Design:
 *	phpMyTicket - ticket reservation system
 * 	Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
 *
 * This file is part of fusionTicket.
 *
 * This file may be distributed and/or modified under the terms of the
 * "GNU General Public License" version 3 as published by the Free
 * Software Foundation and appearing in the file LICENSE included in
 * the packaging of this file.
 *
 * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
 * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE.
 *
 *
 * The "GNU General Public License" (GPL) is available at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * Contact info@noctem.co.uk if any conditions of this licencing isn't
 * clear to you.
 *}

{if $shop_event.event_pm_id}
  <br>
  <form id='catselect' method='post' action='index.php'>
  {ShowFormToken}
  <input type='hidden' name='action' value='addtocart' />
  <input type='hidden' name='event_id' value='{$shop_event.event_id}'>

  <table border=0 width='100%'  cellpadding='5' bgcolor=white>
    <tr>
      <td colspan='3' class="title">
        {!cat_description!}
      </td>
    </tr>
		<tr class='small_table_dark' >
			<th>{!category!}</th>
			<th width='20%'>{!price!}</th>
			<th width='80'>{!order_seats!}</th>
		</tr>
		{cart->maxSeatsAlowed event=$shop_event}
    {category event_id=$shop_event.event_id stats="on"}
      {cycle assign='cycle' name='events' values="tr_1,tr_0" print=NO}
      <tr class='{$cycle}'>
        <td>
    		  {if $user->mode() neq '-1' or $user->logged}
    		    {if $shop_category.category_numbering <>'none'}
              <span id="catcolor" style="background-color:{$shop_category.category_color}">
                <input type="radio" id="category_id_{$shop_category.category_id}" name="category_id" value="{$shop_category.category_id}" {if $category_id eq $shop_category.category_id}checked{/if} onClick="setNum('{$shop_category.category_numbering}')" {if $shop_category.category_free <= 0}disabled="true"{/if}>
              </span>
            {/if}
          {/if}

          <b><label for="category_id_{$shop_category.category_id}">{$shop_category.category_name}</label></b>
        </td>
        <td align='right'>
          {valuta value=$shop_category.category_price|string_format:"%.2f"}
        </td>
        <td align='center'>
          {if $shop_category.category_free==0}
            <span class='error'>{!category_sold!}</span>
          {elseif $seatlimit <= 0}
             <span class="error">{!order_limit!}</span>
          {elseif $shop_category.category_numbering =='none'}
            <select id="category_id_{$shop_category.category_id}" name='mycart[{$shop_category.category_id}][0]' style='float: right;vertical-align:middle;' >
              {section name="myLoop" start=0 loop=$seatlimit+1}
                <option value='{$smarty.section.myLoop.index}' > {$smarty.section.myLoop.index} </option>
              {/section}
            </select>
          {else}
           &nbsp;
          {/if}
        </td>
      </tr>
     {discount event_id=$shop_event.event_id cat_price=$shop_category.category_price category_id=$shop_category.category_id}
        <tr class='{$cycle}'>
          <td > &nbsp;
            <span class='note'>
              <label for="category_id_{$shop_category.category_id}_{$shop_discount.discount_id}"> {!Discount_for!} {$shop_discount.discount_name}
            </span>
          </td>
          <td align='right' >
            {valuta value=$shop_discount.discount_price|string_format:"%.2f"}
          </td>
          <td >
            {if $shop_category.category_free == 0 or $seatlimit <= 0}
              &nbsp;
            {elseif $shop_category.category_numbering <> 'none'}
              &nbsp;
            {else}
            <select id="category_id_{$shop_category.category_id}_{$shop_discount.discount_id}" name='mycart[{$shop_category.category_id}][{$shop_discount.discount_id}]'  style='float: right;vertical-align:middle;' >
              {section name="myLoop" start=0 loop=$seatlimit+1}
                <option value='{$smarty.section.myLoop.index}' > {$smarty.section.myLoop.index} </option>
              {/section}
            </select>
            {/if}
          </td>
        </tr>
      {/discount}
    {/category}
  </table>
  <br />
			<div align='right'>
		        <button title="Next" onclick="validateSelection();" >{!nextbutton!}</button>
			</div>
  </form>
  <br />
  {if $carterror}
     <div class='error' align=center>{$carterror}</div> <br>
  {/if}


{/if}
