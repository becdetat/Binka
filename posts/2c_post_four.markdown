#Roadmap/design goals:

- View and content processing by Slab MVC engine
- No or limited database
- No admin console
- Content generated from static Markdown files, rendered into view template
- Each content file starts with metadata, eg:
  > title: My blog post
  > tags: a, b, c
  > posted: 2010-10-20 15.30
  > permalink: my_blog_post
  > shortlink: 1#
  > 
  > Start of *Markdown* formated content...
- Comment system via Disqus
- Static media folder
- Updates are done on local fork of the site, then just copied to live server