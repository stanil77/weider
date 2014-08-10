<div itemtype="#" itemscope="" class="sdsarticleCat clearfix">
    <div id="smartblogpost-{$post.id_post}">
    <div class="sdsarticleHeader">
         {assign var="options" value=null}
                            {$options.id_post = $post.id_post} 
                            {$options.slug = $post.link_rewrite}
                            <p class='sdstitle_block'><a title="{$post.meta_title}" href='{smartblog::GetSmartBlogLink('smartblog_post',$options)}'>{$post.meta_title}</a></p>
             {assign var="options" value=null}
                        {$options.id_post = $post.id_post}
                        {$options.slug = $post.link_rewrite}
               {assign var="catlink" value=null}
                            {$catlink.id_category = $post.id_category}
                            {$catlink.slug = $post.cat_link_rewrite}
        <div class="post-info">
         <span>{l s='Posted by' mod='smartblog'}&nbsp;<span itemprop="author">{if $smartshowauthor ==1}&nbsp;<i class="icon icon-user"></i>&nbsp; {if $smartshowauthorstyle != 0}{$post.firstname} {$post.lastname}{else}{$post.lastname} {$post.firstname}{/if}</span>{/if} &nbsp;&nbsp;<i class="icon icon-tags"></i>&nbsp; <span itemprop="articleSection" class="articleSection"><a href="{smartblog::GetSmartBlogLink('smartblog_category',$catlink)}">{if $title_category != ''}{$title_category}{else}{$post.cat_name}{/if}</a></span> &nbsp;<span class="comment"> <iframe src="http://www.facebook.com/plugins/comments.php?href={smartblog::GetSmartBlogLink('smartblog_post',$options)}&permalink=1" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:130px; height:16px; padding-top: 5px;" allowTransparency="true"></iframe>
               </span>{if $smartshowviewed ==1}<i class="icon icon-eye-open"></i>&nbsp;{l s=' views' mod='smartblog'} ({$post.viewed}){/if}</span>
         </div>
    </div>
    <div class="articleContent">
          <a itemprop="url" title="{$post.meta_title}" class="imageFeaturedLink">
                    {assign var="activeimgincat" value='0'}
                    {$activeimgincat = $smartshownoimg} 
                    {if ($post.post_img != "no" && $activeimgincat == 0) || $activeimgincat == 1}
              <img itemprop="image" alt="{$post.meta_title}" src="{$modules_dir}/smartblog/images/{$post.post_img}-single-default.jpg" class="imageFeatured">
                    {/if}
          </a>
    </div>
           <div class="sdsarticle-des">
          <span itemprop="description" class="clearfix"><div id="lipsum">
	{$post.short_description}</div></span>
         </div>
        <div class="sdsreadMore">
                  {assign var="options" value=null}
                        {$options.id_post = $post.id_post}  
                        {$options.slug = $post.link_rewrite}  
                         <span class="more"><a title="{$post.meta_title}" href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}" class="r_more">{l s='Read more' mod='smartblog'} </a></span>
        </div>
   </div>
</div>