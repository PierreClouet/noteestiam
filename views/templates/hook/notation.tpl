<div class="tabs">
    <h3>{$title}</h3>
    {if $moyenne != 0}
        <p>Noté {$moyenne} / 5 - {$nb_notes} notes</p>
    {else}
        <p>Pas de note pour ce produit</p>
    {/if}
    <ul>
        {foreach from=$notes item=note}
            <li>{$note.firstname} ({$note.note}/5) :
                <em>{($note.comment)? "{$note.comment}" : 'Pas de commentaire'}</em></li>
        {/foreach}
    </ul>

    {if $logged}
        {if $can_user_note}
            <p>Vous avez déjà noté ce produit</p>
        {else}
            <form action="{$link}" method="post">
                <label for="note">Note :</label>
                <select name="note" id="note">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select><span>/5</span>
                <br/>
                <label for="comment">Commentaire :</label>
                <textarea name="comment" id="comment" cols="30" rows="10"></textarea>
                <input type="hidden" name="id_product" value="{$product.id}"/>
                <input type="submit" value="voter"/>
            </form>
        {/if}
    {else}
        <p>Connectez-vous pour laisser une note</p>
    {/if}
</div>
