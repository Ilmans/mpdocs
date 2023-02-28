<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="<?= route('public.app') ?>">Projects</a>
    <ul class="dropdown-menu">
        <li class="dropdown-header">Projects</li>

        @foreach($dropdownContent as $dropdown)
        <li class="dropdown">
        	<a class="dropdown-item dropdown-toggle" href="<?= $dropdown['url'] ?>"><?= $dropdown['title'] ?></a>
            <ul class="dropdown-menu">
        		<li class="dropdown-header">Versions</li>
        		@foreach($dropdown['versions'] as $version)
             	<li class="dropdown">
             		<a class="dropdown-item dropdown-toggle" href="<?= $version['url'] ?>"><?= $version['version'] ?></a>
                    <ul class="dropdown-menu">
                    	@foreach($version['articles'] as $article)
                        <li>
                        	<a class="dropdown-item" href="<?= $article['url'] ?>"><?= $article['title'] ?></a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endforeach
            </ul>
        </li>
        @endforeach
    </ul>
</li>