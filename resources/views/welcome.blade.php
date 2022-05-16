<h1 id="matcher-microservice-case-study">Matcher microservice Case Study</h1>
<hr>
<h1 id="table-of-contents">Table of Contents</h1>
<ul>
<li><a href="#known-issues">Known Issues</a></li>
<li><a href="#requirements-installation">Requirements &amp; Installation</a></li>
<li><a href="#match-api">Match API</a></li>
</ul>
<hr>
<h1 id="known-issues">Known Issues</h1>
<ul>
<li>This project is not pushed to a live website</li>
<li>Reduce complexity of queries by using ELASTICSEARCH</li>
<li>Filter by direct fields is yet to be implemented</li>
<li>Write unit tests</li>
</ul>
<hr>
<h1 id="requirements-installation">Requirements &amp; Installation</h1>
<ul>
<li>PHP 7.4</li>
<li>Composer 2.0+</li>
<li>Mysql 8.x</li>
<li>Repository link<ul>
<li><a href="https://github.com/skthon/reo">https://github.com/skthon/reo</a></li>
</ul>
</li>
<li><p>Installation and commands</p>
<ul>
<li>Clone the code repository<pre><code>gh repo <span class="hljs-keyword">clone</span> <span class="hljs-title">skthon</span>/reo
git checkout feature/search-profile-matcher-api
</code></pre></li>
<li>After cloning, run the composer command to install packages<pre><code>   composer <span class="hljs-keyword">install</span>
</code></pre></li>
<li>Install mysql and configure the database in .env <pre><code><span class="hljs-attr">DB_CONNECTION</span>=mysql
<span class="hljs-attr">DB_HOST</span>=<span class="hljs-number">127.0</span>.<span class="hljs-number">0.1</span>
<span class="hljs-attr">DB_PORT</span>=<span class="hljs-number">3306</span>
<span class="hljs-attr">DB_DATABASE</span>=reo
<span class="hljs-attr">DB_USERNAME</span>=reo
<span class="hljs-attr">DB_PASSWORD</span>=reopass
</code></pre></li>
<li>Run Migrations<pre><code><span class="hljs-attribute">php artisan migrate</span>
</code></pre></li>
<li><p>Setup demo data by executing this code from tinker. Ideally this should be done using database seeders</p>
<pre><code>php artisan tinker

<span class="hljs-comment"># Create PropertyType model record</span>
$propertyType = \App\Models\PropertyType::create([<span class="hljs-string">'name'</span> =&gt; <span class="hljs-string">'BUILDING'</span>]);

<span class="hljs-comment"># Create Property model record</span>
$property = \App\Models\Property::create([
    <span class="hljs-string">'name'</span> =&gt; <span class="hljs-string">'Awesome house in the middle of my town'</span>,
    <span class="hljs-string">'address'</span> =&gt; <span class="hljs-string">'Main street 17, 12456 Berlin'</span>,
    <span class="hljs-string">'price'</span> =&gt; <span class="hljs-number">1500000</span>,
    <span class="hljs-string">"area"</span> =&gt; <span class="hljs-string">"180"</span>,
    <span class="hljs-string">"year_of_construction"</span> =&gt; <span class="hljs-string">"2010"</span>,
    <span class="hljs-string">"rooms"</span> =&gt; <span class="hljs-string">"5"</span>,
    <span class="hljs-string">"heating_type"</span> =&gt; <span class="hljs-string">"gas"</span>,
    <span class="hljs-string">"parking"</span> =&gt; <span class="hljs-keyword">true</span>,
    <span class="hljs-string">"return_actual"</span> =&gt; <span class="hljs-string">"12.8"</span>
]);
$property-&gt;property_type()-&gt;associate($propertyType);
$property-&gt;save();

<span class="hljs-comment"># Create Search Profile record</span>
\App\Models\SearchProfile::create([
    <span class="hljs-string">'name'</span> =&gt; <span class="hljs-string">"Looking for any Awesome real estate!"</span>,
    <span class="hljs-string">'min_price'</span> =&gt; <span class="hljs-number">0</span>,
    <span class="hljs-string">'max_price'</span> =&gt; <span class="hljs-number">2000000</span>,
    <span class="hljs-string">'min_area'</span> =&gt; <span class="hljs-number">150</span>,
    <span class="hljs-string">'max_area'</span> =&gt; <span class="hljs-keyword">null</span>,
    <span class="hljs-string">'min_year_of_construction'</span> =&gt; <span class="hljs-number">2010</span>,
    <span class="hljs-string">'max_year_of_construction'</span> =&gt; <span class="hljs-keyword">null</span>,
    <span class="hljs-string">'min_rooms'</span> =&gt; <span class="hljs-number">4</span>,
    <span class="hljs-string">'max_rooms'</span> =&gt; <span class="hljs-keyword">null</span>,
    <span class="hljs-string">'min_return_actual'</span> =&gt; <span class="hljs-number">15</span>,
    <span class="hljs-string">'max_return_actual'</span> =&gt; <span class="hljs-keyword">null</span>
]);
</code></pre></li>
</ul>
</li>
</ul>
<hr>
<h1 id="match-api">Match API</h1>
<ul>
<li>GET request</li>
<li>URLS<ul>
<li><a href="http://localhost:8081/api/match/">http://localhost:8081/api/match/</a><property_id></li>
</ul>
</li>
<li>Accepted parameters<ul>
<li><code>property_id</code><ul>
<li>Accepts valid property UUID, This doesn&#39;t accept id since its bad to expose primary key to the user</li>
</ul>
</li>
</ul>
</li>
<li><p>Response Body</p>
<pre><code class="lang-json">{
  <span class="hljs-attr">"property"</span>: {
      <span class="hljs-attr">"id"</span>: <span class="hljs-number">1</span>,
      <span class="hljs-attr">"uuid"</span>: <span class="hljs-string">"964e8976-89cf-4bc6-af10-06e8384a0d6f"</span>,
      <span class="hljs-attr">"user_uuid"</span>: <span class="hljs-literal">null</span>,
      <span class="hljs-attr">"property_type_uuid"</span>: <span class="hljs-string">"964e896a-98cb-4c73-b0e3-8ef6a867dcf4"</span>,
      <span class="hljs-attr">"name"</span>: <span class="hljs-string">"Awesome house in the middle of my town"</span>,
      <span class="hljs-attr">"address"</span>: <span class="hljs-string">"Main street 17, 12456 Berlin"</span>,
      <span class="hljs-attr">"price"</span>: <span class="hljs-number">1500000</span>,
      <span class="hljs-attr">"area"</span>: <span class="hljs-number">180</span>,
      <span class="hljs-attr">"year_of_construction"</span>: <span class="hljs-number">2010</span>,
      <span class="hljs-attr">"rooms"</span>: <span class="hljs-number">5</span>,
      <span class="hljs-attr">"heating_type"</span>: <span class="hljs-string">"gas"</span>,
      <span class="hljs-attr">"parking"</span>: <span class="hljs-literal">true</span>,
      <span class="hljs-attr">"return_actual"</span>: <span class="hljs-string">"12.80"</span>,
      <span class="hljs-attr">"status"</span>: <span class="hljs-literal">true</span>,
      <span class="hljs-attr">"created_at"</span>: <span class="hljs-string">"2022-05-15T18:49:38.000000Z"</span>,
      <span class="hljs-attr">"updated_at"</span>: <span class="hljs-string">"2022-05-15T18:49:47.000000Z"</span>
  },
  <span class="hljs-attr">"matching_profiles"</span>: [{
      <span class="hljs-attr">"searchProfileId"</span>: <span class="hljs-string">"964e898d-cf87-4edf-995e-dfab43a752ed"</span>,
      <span class="hljs-attr">"score"</span>: <span class="hljs-string">"4.0"</span>,
      <span class="hljs-attr">"strictMatchesCount"</span>: <span class="hljs-number">3</span>,
      <span class="hljs-attr">"looseMatchesCount"</span>: <span class="hljs-number">2</span>
  }]
}
</code></pre>
</li>
<li><p>Errors</p>
<ul>
<li>Internal error with 500 response</li>
<li>Invalid input with 400 response</li>
</ul>
</li>
</ul>
<hr>
