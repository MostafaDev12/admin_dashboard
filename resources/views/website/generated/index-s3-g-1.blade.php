<a class="{{ $item1->content['f1'] ?? '' }}" href="{{ $item1->content['f2'] ?? '' }}" style="background-image:
                linear-gradient(180deg, rgba(1,38,48,.04), rgba(1,38,48,.88)),
                url('{{ $item1->content['f3'] ?? '' }}');" {{ new \Illuminate\View\ComponentAttributeBag($item1->content['attributes_1'] ?? []) }}>
                <span {{ new \Illuminate\View\ComponentAttributeBag($item1->content['attributes_2'] ?? []) }}>{{ $item1->content['f4'] ?? '' }}</span>
                <small {{ new \Illuminate\View\ComponentAttributeBag($item1->content['attributes_3'] ?? []) }}>{{ $item1->content['f5'] ?? '' }}</small>
            </a>