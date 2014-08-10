{capture name=path}<a href="{smartblog::GetSmartBlogLink('smartblog')}">{l s='All Blog News' mod='smartblog'}</a><span class="navigation-pipe">{$navigationPipe}</span>{$meta_title}{/capture}
<div id="content" class="block">
   <div itemtype="#" itemscope="" id="sdsblogArticle" class="blog-post">
   		<div class="page-item-title">
   			<h1>{$meta_title}</h1>
   		</div>
      <div class="post-info">
         {assign var="catOptions" value=null}
                        {$catOptions.id_category = $id_category}
                        {$catOptions.slug = $cat_link_rewrite}
                     <span>
               {l s='Posted by ' mod='smartblog'} {if $smartshowauthor ==1}&nbsp;<i class="icon icon-user"></i>&nbsp;<span itemprop="author">{if $smartshowauthorstyle != 0} {$firstname} {$lastname}{else} {$lastname} {$firstname}{/if}</span>&nbsp;<i class="icon icon-calendar"></i>&nbsp;<span itemprop="dateCreated">{$created|date_format}</span>{/if}&nbsp;&nbsp;<i class="icon icon-tags"></i>&nbsp;<span itemprop="articleSection" class="articleSection"><a href="{smartblog::GetSmartBlogLink('smartblog_category',$catOptions)}">{$title_category}</a></span> &nbsp;

               	</span>
                  <a title="" style="display:none" itemprop="url" href="#"></a>
      </div>
      <div itemprop="articleBody">
            <div id="lipsum" class="articleContent">
                    {assign var="activeimgincat" value='0'}
                    {$activeimgincat = $smartshownoimg} 
                    {if ($post_img != "no" && $activeimgincat == 0) || $activeimgincat == 1}
                        <a id="post_images" href="{$modules_dir}/smartblog/images/{$post_img}-single-default.jpg"><img src="{$modules_dir}/smartblog/images/{$post_img}-single-default.jpg" alt="{$meta_title}"></a>
                    {/if}
             </div>
            <div class="sdsarticle-des">
               {$content}
            </div>
            {if $tags != ''}
                <div class="sdstags-update">
                    <span class="tags"><b>{l s='Tags:' mod='smartblog'} </b> 
                        {foreach from=$tags item=tag}
                            {assign var="options" value=null}
                            {$options.tag = $tag.name}
                            <a title="tag" href="{smartblog::GetSmartBlogLink('smartblog_tag',$options)}">{$tag.name}</a>
                        {/foreach}
                    </span>
                </div>
           {/if}
      </div>
     
      <div class="sdsarticleBottom">
      	<div class="fb-comments" data-href="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" data-width="100%" data-numposts="5" data-colorscheme="light"></div>

        {$HOOK_SMART_BLOG_POST_FOOTER}
      </div>
   </div>


{if isset($smartcustomcss)}
    <style>
        {$smartcustomcss}
    </style>
{/if}
