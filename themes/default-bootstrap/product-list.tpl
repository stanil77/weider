{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if isset($products) && $products}
	{*define numbers of product per line in other page for desktop*}
	{if $page_name !='index' && $page_name !='product'}
		{assign var='nbItemsPerLine' value=3}
		{assign var='nbItemsPerLineTablet' value=2}
		{assign var='nbItemsPerLineMobile' value=3}
		
	{else}
		{assign var='nbItemsPerLine' value=4}
		{assign var='nbItemsPerLineTablet' value=3}
		{assign var='nbItemsPerLineMobile' value=2}

	{/if}
	{*define numbers of product per line in other page for tablet*}
	{assign var='nbLi' value=$products|@count}
	{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
	{math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
	<!-- Products list -->
	<ul{if isset($id) && $id} id="{$id}"{/if} class="product_list grid row{if isset($class) && $class} {$class}{/if}">
	{foreach from=$products item=product name=products}
		{math equation="(total%perLine)" total=$smarty.foreach.products.total perLine=$nbItemsPerLine assign=totModulo}
		{math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineTablet assign=totModuloTablet}
		{math equation="(total%perLineT)" total=$smarty.foreach.products.total perLineT=$nbItemsPerLineMobile assign=totModuloMobile}
		{if ($product.specific_prices.reduction_type == 'amount')&&$product.specific_prices.reduction>0}
			{assign var="s_reduction" value=array_filter(explode(".",$product.specific_prices.reduction|string_format:"%.2f"))}
			{assign var="reductionPriceLev" value=$s_reduction[0]}
			{if isset($s_reduction[1])}
					{assign var="reductionPriceStotinki" value=$s_reduction[1]}
			{else}
				{assign var="reductionPriceStotinki" value=0}
			{/if}
		{/if}
		
		{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
			{assign var="s_price" value=array_filter(explode(".",$product.price))}
			{if $product.price == 0}
				{assign var="productPriceLev" value=0}
			{else}
				{assign var="productPriceLev" value=$s_price[0]}
			{/if}
			{assign var="productPriceStotinki" value=0}
			{if isset($s_price[1])}
				{if $s_price[1] < 10}
					{$productPriceStotinki=10*$s_price[1]}
				{else}
					{$productPriceStotinki=$s_price[1]}
				{/if}
			{else}
				{$productPriceStotinki=0}
			{/if}
		{/if}

		{if $totModulo == 0}{assign var='totModulo' value=$nbItemsPerLine}{/if}
		{if $totModuloTablet == 0}{assign var='totModuloTablet' value=$nbItemsPerLineTablet}{/if}
		{if $totModuloMobile == 0}{assign var='totModuloMobile' value=$nbItemsPerLineMobile}{/if}
		<li class="ajax_block_product{if $page_name == 'index' || $page_name == 'product'} col-xs-12 col-sm-4 col-md-3{else} col-xs-12 col-sm-6 col-md-4{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLine == 0} last-in-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLine == 1} first-in-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModulo)} last-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 0} last-item-of-tablet-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineTablet == 1} first-item-of-tablet-line{/if}{if $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 0} last-item-of-mobile-line{elseif $smarty.foreach.products.iteration%$nbItemsPerLineMobile == 1} first-item-of-mobile-line{/if}{if $smarty.foreach.products.iteration > ($smarty.foreach.products.total - $totModuloMobile)} last-mobile-line{/if}">
			<div class="product-container" {if isset($product.packItems) && $product.packItems|@count >0}style="background: -moz-linear-gradient(top,  red 0%, black 100%, black 100%);background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,red), color-stop(100%,black), color-stop(100%,black)); background: -webkit-linear-gradient(top,  red 0%,black 100%,black 100%);background: -o-linear-gradient(top,  red 0%,black 100%,black 100%);  background: -ms-linear-gradient(top,  red 0%,black 100%,black 100%);background: linear-gradient(top,  red 0%,black 100%,black 100%);"			"
			{/if} 
			itemscope itemtype="http://schema.org/Product">
				<div class="left-block">
					<div class="product-image-container">
						<a class="product_img_link"	href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url">
							<img class="replace-2x img-responsive" src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home_default')|escape:'html':'UTF-8'}" alt="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" title="{if !empty($product.legend)}{$product.legend|escape:'html':'UTF-8'}{else}{$product.name|escape:'html':'UTF-8'}{/if}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} itemprop="image" />
						</a>
						{if isset($quick_view) && $quick_view}
							<div class="quick-view-wrapper-mobile">
							<a class="quick-view-mobile" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}">
								<i class="icon-eye-open"></i>
							</a>
						</div>
						<a class="quick-view" href="{$product.link|escape:'html':'UTF-8'}" rel="{$product.link|escape:'html':'UTF-8'}">
							<span>{l s='Quick view'}</span>
						</a>
						{/if}
						{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))&&$page_name!='product'}
							<div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
								{assign var="s_priceshown_h" value="false"}
								{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
									{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
										{hook h="displayProductPriceBlock" product=$product type="old_price"}
										<span class="old-price product-price">
											{displayWtPrice p=$product.price_without_reduction}
										</span>
										{*
										<span itemprop="price" class="price product-price">
											{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
										</span>
										*}
										<span itemprop="price" class="spestqvate-label" style="margin-top:6px; display:initial;">{$productPriceLev}<sup class="spestqvate-label-stotinki">{$productPriceStotinki}{if $productPriceStotinki < 10}0{/if}</sup><span class="spestqvate-label-valuta">лева</span></span>
										<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
										{$s_priceshown_h="true"}
										{if $product.specific_prices.reduction_type == 'percentage'}
											<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
										{elseif $product.specific_prices.reduction_type == 'amount'}
											{*<span class="price-percent-reduction">спестявате:{convertPrice price=$product.specific_prices.reduction}</span>*}
										{/if}
									{elseif $product.price_without_reduction>$product.price}
										{hook h="displayProductPriceBlock" product=$product type="old_price"}
										<span class="old-price product-price">
										{displayWtPrice p=$product.price_without_reduction}
										</span>
										{*
										<span itemprop="price" class="price product-price">
											{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
										</span>
										*}
										<span itemprop="price" class="spestqvate-label" style="margin-top:6px; display:initial;">{$productPriceLev}<sup class="spestqvate-label-stotinki">{$productPriceStotinki}{if $productPriceStotinki < 10}0{/if}</sup><span class="spestqvate-label-valuta">лева</span></span>
										<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
										{$s_priceshown_h="true"}
										{hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
										<span class="price-percent-reduction">{((1-($product.price_without_reduction/$product.price)) * 100)|round:0}%</span>
									{/if}
									{if $s_priceshown_h=="false"}
									{*
									<span itemprop="price" class="price product-price">
											{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
									</span>
									*}
									<span itemprop="price" class="spestqvate-label" style="margin-top:6px; display:initial;">{$productPriceLev}<sup class="spestqvate-label-stotinki">{$productPriceStotinki}{if $productPriceStotinki < 10}0{/if}</sup><span class="spestqvate-label-valuta">лева</span></span>
									
									<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
									{/if}
									{hook h="displayProductPriceBlock" product=$product type="price"}
									{hook h="displayProductPriceBlock" product=$product type="unit_price"}
								{/if}
							</div>
						{/if}
						{if isset($product.new) && $product.new == 1}
							<a class="new-box" href="{$product.link|escape:'html':'UTF-8'}">
								<span class="new-label">{l s='New'}</span>
							</a>
						{/if}
                        {if isset($product.packItems) && $product.packItems|@count >0}
							<a class="pack-box" href="{$product.link|escape:'html':'UTF-8'}">
								<span class="pack-label">{l s='Стак!'}</span>
							</a>
							<a class="spestqvate-box" href="{$product.link|escape:'html':'UTF-8'}">
								
								<span class="spestqvate-label">{$reductionPriceLev}<sup class="spestqvate-label-stotinki">{$reductionPriceStotinki}</sup><span class="spestqvate-label-valuta">лева</span></span>
								{*
								<span class="spestqvate-label-stotinki">{$reductionPriceStotinki}</span>
								*}
							</a>
						{elseif isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price}
							<a class="sale-box" href="{$product.link|escape:'html':'UTF-8'}">
								<span class="sale-label">{l s='Sale!'}</span>
							</a>
						{/if}

					</div>
					{hook h="displayProductDeliveryTime" product=$product}
					{hook h="displayProductPriceBlock" product=$product type="weight"}
				</div>
				<div class="right-block">
					<h5 itemprop="name">
						{if isset($product.pack_quantity) && $product.pack_quantity}{$product.pack_quantity|intval|cat:' x '}{/if}
						<a class="product-name" href="{$product.link|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}" itemprop="url" >
							{$product.name|truncate:45:'...'|escape:'html':'UTF-8'}
						</a>
					</h5>
					{hook h='displayProductListReviews' product=$product}
					<p class="product-desc" itemprop="description">
						{$product.description_short|strip_tags:'UTF-8'|truncate:360:'...'}
					</p>
					{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
					<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="content_price">
						{if isset($product.show_price) && $product.show_price && !isset($restricted_country_mode)}
							{assign var="s_priceshown" value="false"}
							{if isset($product.specific_prices) && $product.specific_prices && isset($product.specific_prices.reduction) && $product.specific_prices.reduction > 0}
								{hook h="displayProductPriceBlock" product=$product type="old_price"}
								<span class="old-price product-price">
									{displayWtPrice p=$product.price_without_reduction}
								</span>
								{*
								<span itemprop="price" class="price product-price">
									{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
								</span>
								*}
								<span itemprop="price" class="spestqvate-label" style="margin-top:6px; display:initial;">{$productPriceLev}<sup class="spestqvate-label-stotinki">{$productPriceStotinki}</sup><span class="spestqvate-label-valuta">лева</span></span>
										<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
								{$s_priceshown="true"}
								<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
							
								{hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
								{if $product.specific_prices.reduction_type == 'percentage'}
									<span class="price-percent-reduction">-{$product.specific_prices.reduction * 100}%</span>
								{elseif $product.specific_prices.reduction_type == 'amount'}
									{*<span class="price-percent-reduction">спестявате:{convertPrice price=$product.specific_prices.reduction}</span>*}
								{/if}
							{elseif $product.price_without_reduction>$product.price}
								{hook h="displayProductPriceBlock" product=$product type="old_price"}
								<span class="old-price product-price">
									{displayWtPrice p=$product.price_without_reduction}
								</span>
								{*
								<span itemprop="price" class="price product-price">
									{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
								</span>
								*}
								<span itemprop="price" class="spestqvate-label" style="margin-top:6px; display:initial;">{$productPriceLev}<sup class="spestqvate-label-stotinki">{$productPriceStotinki}</sup><span class="spestqvate-label-valuta">лева</span></span>
								{$s_priceshown="true"}
								<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
							
								{hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"}
								<span class="price-percent-reduction">{((1-($product.price_without_reduction/$product.price)) * 100)|round:0}%</span>
							{/if}
							{if $s_priceshown=="false"}
								{*}
								<span itemprop="price" class="price product-price">
									{if !$priceDisplay}{convertPrice price=$product.price}{else}{convertPrice price=$product.price_tax_exc}{/if}
								</span>
								*}
								<span itemprop="price" class="spestqvate-label" style="margin-top:6px; display:initial;">{$productPriceLev}<sup class="spestqvate-label-stotinki">{$productPriceStotinki}</sup><span class="spestqvate-label-valuta">лева</span></span>
								{$s_priceshown="true"}
								<meta itemprop="priceCurrency" content="{$currency->iso_code}" />
							{/if}
							{hook h="displayProductPriceBlock" product=$product type="price"}
							{hook h="displayProductPriceBlock" product=$product type="unit_price"}
						{/if}
					</div>
					{/if}
					<div class="button-container">
						{if ($product.id_product_attribute == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product.available_for_order && !isset($restricted_country_mode) && $product.minimal_quantity <= 1 && $product.customizable != 2 && !$PS_CATALOG_MODE}
							{if ($product.allow_oosp || $product.quantity > 0)}
								{if isset($static_token)}
									<a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, "add=1&amp;id_product={$product.id_product|intval}&amp;token={$static_token}", false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart'}" data-id-product="{$product.id_product|intval}">
										<span>{l s='Add to cart'}</span>
									</a>
								{else}
									<a class="button ajax_add_to_cart_button btn btn-default" href="{$link->getPageLink('cart',false, NULL, 'add=1&amp;id_product={$product.id_product|intval}', false)|escape:'html':'UTF-8'}" rel="nofollow" title="{l s='Add to cart'}" data-id-product="{$product.id_product|intval}">
										<span>{l s='Add to cart'}</span>
									</a>
								{/if}
							{else}
								<span class="button ajax_add_to_cart_button btn btn-default disabled">
									<span>{l s='Add to cart'}</span>
								</span>
							{/if}
						{/if}
						{*
						<a itemprop="url" class="button lnk_view btn btn-default" href="{$product.link|escape:'html':'UTF-8'}" title="{l s='View'}">
							<span>{l s='More'}</span>
						</a>
						*}
					</div>
					{if isset($product.color_list)}
						<div class="color-list-container">{$product.color_list}</div>
					{/if}
					<div class="product-flags">
						{if (!$PS_CATALOG_MODE AND ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
							{if isset($product.online_only) && $product.online_only}
								<span class="online_only">{l s='Online only'}</span>
							{/if}
						{/if}
						{if isset($product.on_sale) && $product.on_sale && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
							{elseif isset($product.reduction) && $product.reduction && isset($product.show_price) && $product.show_price && !$PS_CATALOG_MODE}
								<span class="discount">{l s='Reduced price!'}</span>
							{/if}
					</div>
					{*
					{if (!$PS_CATALOG_MODE && $PS_STOCK_MANAGEMENT && ((isset($product.show_price) && $product.show_price) || (isset($product.available_for_order) && $product.available_for_order)))}
						{if isset($product.available_for_order) && $product.available_for_order && !isset($restricted_country_mode)}
							<span itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="availability">
								{if ($product.allow_oosp || $product.quantity > 0)}
									<span class="{if $product.quantity <= 0 && !$product.allow_oosp}out-of-stock{else}available-now{/if}">
										<link itemprop="availability" href="http://schema.org/InStock" />{if $product.quantity <= 0}{if $product.allow_oosp}{if isset($product.available_later) && $product.available_later}{$product.available_later}{else}{l s='In Stock'}{/if}{else}{l s='Out of stock'}{/if}{else}{if isset($product.available_now) && $product.available_now}{$product.available_now}{else}{l s='In Stock'}{/if}{/if}
									</span>
								{elseif (isset($product.quantity_all_versions) && $product.quantity_all_versions > 0)}
									<span class="available-dif">
										<link itemprop="availability" href="http://schema.org/LimitedAvailability" />{l s='Product available with different options'}
									</span>
								{else}
									<span class="out-of-stock">
										<link itemprop="availability" href="http://schema.org/OutOfStock" />{l s='Out of stock'}
									</span>
								{/if}
							</span>
						{/if}
					{/if}
					*}
				</div>
				
				{if $page_name != 'index'}
	 				<div class="functional-buttons clearfix">
						{hook h='displayProductListFunctionalButtons' product=$product}
						{if isset($comparator_max_item) && $comparator_max_item}
							<div class="compare">
								<a class="add_to_compare" href="{$product.link|escape:'html':'UTF-8'}" data-id-product="{$product.id_product}">{l s='Add to Compare'}</a>
							</div>
						{/if}
					</div>
				{/if}
				
			</div><!-- .product-container> -->
		</li>
	{/foreach}
	</ul>
{addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
{addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
{addJsDef comparator_max_item=$comparator_max_item}
{addJsDef comparedProductsIds=$compared_products}
{/if}
