# Youarel *for Craft CMS*

Pronounced *"URL"*, this plugin adds a small collection of filters and functions that can query and modify a url string.

## Segment
Grab a certain segment of a given URL string. You can query a url with a filter or function.

#### Settings
| Options         | Description
 ---------------- | ---------------------
| Positive number | Return the segment of the given number from the start of the URL.
| Negative number | Return the segment of the given number from the end of the URL.
| Zero            | Return the url without an segments
| 'first'         | Alias of ```1```
| 'last'          | Return the last segment

#### Usage
Returns 2nd segment of current page url
```
{{ segment(2) }}
```

Returns 2nd url segment : ```seg```
```
{{ segment("http://www.website.com/segment/seg/s", 2) }}
{{ "http://www.website.com/segment/seg/s"|segment(2) }}
```

Returns the website url without any segments : ```http://www.website.com```
```
{{ segment("http://www.website.com/segment/seg/s", 0) }}
{{ "http://www.website.com/segment/seg/s"|segment(0) }}
```

Returns the last segment : ```s```
```
{{ segment("http://www.website.com/segment/seg/s", 'last' }}
{{ "http://www.website.com/segment/seg/s"|segment('last' }}
```

Returns the first segment : ```segment```
```
{{ segment("http://www.website.com/segment/seg/s", 'first' }}
{{ "http://www.website.com/segment/seg/s"|segment('first' }}
```

Returns the third segment from the end : ```segment```
```
{{ segment("http://www.website.com/segment/seg/s", -3) }}
{{ "http://www.website.com/segment/seg/s"|segment(-3) }}
```


## Params
Add or update parameters within a string that is a url.

#### Settings
| Type   | Description
 ------- | ---------------------
| String | Add a single string to add the given string to the url without a value. Or add two string parameters, the first will be added as a variable, the second as the value.
| Array  | Add an associative array to add multiple values to the url.

#### Usage
URL and just one string, results in adding the value to the end of the url without editing any existing parameters
```
{{ params('http://www.website.com?foo=bar', 'test') }}
```
```
http://www.website.com?foo=bar&test
```

URL and an array will ignore any other strings passed. Any variables that already exist in the url will be overwritten. Everything else will be added to the end.
```
{{ params('http://www.website.com?foo=bar', {'foo':'bar', 'ping':'pong'}) }}
```
```
http://www.website.com?foo=bar&ping=pong
```

URL and two additional string variables will be added as a variable and value respectively.
```
{{ params('http://www.website.com', 'foo', 'bar') }}
```
```
http://www.website.com?foo=bar
```

Just two strings variables will be added as a variable and value respectively. Ommitting a url will fallback to the current to the current page url
```
{{ params('foo', 'bar') }}
```
```
http://www.[current-url].com?foo=bar
```

Just one string will be added as a value. Ommitting a url will fallback to the current to the current page url
```
{{ params('test') }}
```
```
http://www.[current-url].com?test
```

#### Advance Usage
Using Craft ```#{...}``` syntax, you can use this to produce some interesting results
```
<a href="{{
	params('mailto:mark@marknotton.uk', {
		'subject':"I love your website #{siteName}",
		'body':"I was on '#{ entry.title|default('your website')}' and had to get in touch"
	})
}}">Email Link</a>
```
```
<a href="&#10;mailto:mark@marknotton.uk?subject=I love your website Craft Master&amp;body=I was on 'Welcome to Craft Master' and had to get in touch&#10;">Email Link</a>
```

## Local
A very quick check to see if you are on a local or live server. Returns ```Boolean```
```
{{ local }}
```
