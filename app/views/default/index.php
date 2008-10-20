<h1>It's alive!</h1>
<p>And, some content from the db: <? $posts->each(function($post){ echo $post->author.' ';}) ?></p>
<p><?= count($posts) ?></p>