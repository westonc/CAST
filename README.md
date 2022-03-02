# CAST | Content-addressed style templating

## The Basic Idea

A template is plain vanilla XHTML. There's nothing else inside of it. You don't mark it up with templating syntax at all -- not even in the way you would with something like TAL or Petal (though this is close).

Instead, you address your content into different elements inside the document using CSS or XPath selectors which specify those elements

## Usage

```
$t = new Template('yourfile.html');
$t->fill('#nav',$someMarkup);
$t->fill('#header',$otherMarkup);
echo $t->asXML();
```
	
Or, assuming the following contents for a file called "content.cas":

```
.col #foot {
    content: 'footer content';
}

#content {
    content: file_get_contents('pangolin.txt');
}
```

you pass that to your Template, and choose the fillByCAS method.

```
$t = new Template('yourfile.html','content.cas');
$t->fillByCAS();
echo $t->asXML();
```
	
The script cssfill.php that's part of this very rough repo uses this later metod, taking just such a content addressing file as its first argument, an xhtml file as its second, and writing the combined result to stdout (try ./cssfill.php pangolin.cas pangolin.html as a sample invocation).

I've successfully used CAST in a few personal projects (a personal blog among them), but it should be noted it's still essentially an experiment. Be wary of using it in an important production environment. I'd love to hear from you if you do find it interesting or useful, however.
