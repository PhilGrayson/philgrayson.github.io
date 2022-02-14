---
layout: post
title: Fake reuse of Confluence page names within a space
---

[![Confluence pages with the same name](/assets/images/2022-02/confluence.png){: width="300" }](/assets/images/2021-02/confluence.png)


Pages still need a unique name, but a unique identifer in the page name can
be hidden with some Javascript. The implementation below hides page titles
after a dash.

For example, page titles like:

* How-To - Make Breakfast
* How-To - Make Lunch

Would both be transformed to "How-To".

Drop this snippet into the bottom of the space's main HTML document via the
Space admin, Look and Feel tab.

{% highlight javascript %}
<script type="text/javascript">
(function() {
    var expr = / - [^-]+$/g;
    function cleanupText() {
       var text = $(this).text().trim();
       if (expr.test(text)) {
          $(this).text(text.replace(expr, ""));
        }
    }
    function cleanup() {
      // Sidebar text
      $('.plugin_pagetree_children_content a').each(cleanupText);
      $('#title-text').each(cleanupText);
    }
    $(document).ready(cleanup);
    $(document).ajaxSuccess(cleanup);
})();
</script>
{% endhighlight %}
