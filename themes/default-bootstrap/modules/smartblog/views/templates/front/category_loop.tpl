<div itemtype="#" itemscope="" class="sdsarticleCat clearfix">
    <div id="smartblogpost-{$post.id_post}" class="smartblogff">
        <div class="articleContent">
            {assign var="options" value=null}
            {$options.id_post = $post.id_post}
            {$options.slug = $post.link_rewrite}
            <a href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}" itemprop="url" title="{$post.meta_title}" class="imageFeaturedLink">
                {assign var="activeimgincat" value='0'}
                {$activeimgincat = $smartshownoimg}
                {if ($post.post_img != "no" && $activeimgincat == 0) || $activeimgincat == 1}
                    <img itemprop="image" alt="{$post.meta_title}"
                         src="{$modules_dir}/smartblog/images/{$post.post_img}.jpg" class="imageFeatured">
                {/if}
                <span class="more">{l s='Подробнее' mod='smartblog'}</span>
            </a>
        </div>
        {*<div class="sdsreadMore">*}
            {*{assign var="options" value=null}*}
            {*{$options.id_post = $post.id_post}*}
            {*{$options.slug = $post.link_rewrite}*}
            {*<span class="more"><a title="{$post.meta_title}"*}
                                  {*href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}"*}
                                  {*class="r_more">{l s='Read more' mod='smartblog'} </a></span>*}
        {*</div>*}
        <div class="articleInner">
           <div class="sdsarticleHeader">
                {assign var="options" value=null}
                {$options.id_post = $post.id_post}
                {$options.slug = $post.link_rewrite}
                <p class='sdstitle_block'><a title="{$post.meta_title}"
                                             href='{smartblog::GetSmartBlogLink('smartblog_post',$options)}'>{$post.meta_title}</a>
                </p>
                {assign var="options" value=null}
                {$options.id_post = $post.id_post}
                {$options.slug = $post.link_rewrite}
                {assign var="catlink" value=null}
                {$catlink.id_category = $post.id_category}
                {$catlink.slug = $post.cat_link_rewrite}
            </div>

            <div class="sdsarticle-des">
              <span itemprop="description" class="clearfix"><div id="lipsum">{$post.short_description}</div></span>
            </div>
        </div>


    </div>
</div>