{extends file="parent:frontend/register/login.tpl"}

{* Existing customer *}
{block name='frontend_register_login_form'}

    {block name='port1hybridauth_frontend_register_login_buttons'}
    <div class="port1--hybrid--auth">
        {* generic iterator through provided / configured authsources *}
        {foreach from=$providers item=providerLabel key=providerKey}
            {block name='port1hybridauth_frontend_register_login_button'}
            <a class="single--sign--on {$providerKey|lower} btn" href="{url controller=socialUser action=login provider=$providerKey sTarget=$sTarget sTargetAction=$sTargetAction}">
                <span class="fa fa-{$providerKey|lower}"></span>
                {$providerLabel}
            </a>
            {/block}
        {/foreach}
    </div>
    {/block}

    {$smarty.block.parent}
{/block}
