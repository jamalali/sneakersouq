<x-layout>
  <x-slot name="header">  
    {{ __('Agenty Agents') }}
  </x-slot>

    <table class="table is-bordered is-narrow is-striped is-fullwidth">
        <thead>
            <tr>
                <th>Name</th>
                <th>ID</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($agents as $agent)
                <tr>
                    <td>
                        {{ $agent['title'] }}
                    </td>
                    <td>
                        {{ $agent['id'] }}
                    </td>
                    <td>
                        <a href="{{ route('agents.show', ['agentId' => $agent['id']]) }}">
                            Details
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-layout>