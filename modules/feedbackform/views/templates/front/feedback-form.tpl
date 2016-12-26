<div class="hidden">
    <form action="{$module_dir}feedbackform-ajax.php" id="feedbackform" class="feedbackform">
        <div class="message"></div>
        {if $title}
            <h1 class="title">{$title}</h1>
        {/if}
        {if $text}
            <p class="text">{$text}</p>
        {/if}
        <div class="clearfix">
            {foreach from=$fields item=field name=fields}
                {if isset($field[2]) && $field[2]}
                    {assign var=required value=1}
                {else}
                    {assign var=required value=0}
                {/if}
                {if isset($field[3]) && $field[3]}
                    {assign var=validate value=$field[3]}
                {else}
                    {assign var=validate value=0}
                {/if}
                {if isset($field[1])}
                    <div class="col-lg-6 col-sm-12 form-group">
                        {if isset($field[0]) && $field[0]}
                            {if $field[0] == 'text'}
                                <input type="text"
                                       class="{if $required}is_required {/if}form-control{if $validate} validate{/if}"
                                       name="field_{$smarty.foreach.fields.index}"
                                       placeholder="{$field[1]}{if $required}*{/if}"
                                        {if $required} required {/if}
                                        {if $validate} data-validate="{$validate}"{/if}/>
                            {elseif $field[0] == 'textarea'}
                                <textarea type="text"
                                          class="{if $required}is_required {/if}form-control{if $validate} validate{/if}"
                                          name="field_{$smarty.foreach.fields.index}"
                                          placeholder="{$field[1]}{if $required}*{/if}"
                                        {if $required} required {/if}
                                        {if $validate} data-validate="{$validate}"{/if}></textarea>
                            {else}
                                <input type="text"
                                       class="{if $required}is_required {/if}form-control{if $validate} validate{/if}"
                                       name="field_{$smarty.foreach.fields.index}"
                                       placeholder="{$field[1]}{if $required}*{/if}"
                                        {if $required} required {/if}
                                        {if $validate} data-validate="{$validate}"{/if}/>
                            {/if}
                        {else}
                            <input type="text"
                                   class="{if $required}is_required {/if}form-control{if $validate} validate{/if}"
                                   name="field_{$smarty.foreach.fields.index}"
                                   placeholder="{$field[1]}{if $required}*{/if}"
                                    {if $required} required {/if}
                                    {if $validate} data-validate="{$validate}"{/if}/>
                        {/if}
                    </div>
                {/if}
            {/foreach}
            {if $id_product}
                <input type="hidden" name="id" id="id" value="{$id_product}">
            {/if}
        </div>
        <button name="submitFeedbackForm" id="submitFeedbackForm">{l s="Submit" mod="feedbackform"}</button>
    </form>
</div>