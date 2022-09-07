<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    #cell {
        background-color: #000000;
        color: #ffffff;
    }

    .cell {
        background-color: #000000;
        color: #ffffff;
    }

    tr td {
        background-color: #ffffff;
    }

    tr > td {
        border-bottom: 1px solid #000000;
    }
</style>
<table>
    <thead>
    <tr>
        @foreach($export_columns as $column => $alias)
            <th>{{ $alias }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            @foreach($export_columns as $column => $alias)
                @php
                    if ($dot_position = mb_stripos($column, '.')) {
                        $needle_array = mb_substr($column, 0, $dot_position);
                        $result = $item->$needle_array;
                        if (is_object($result))
                            $result = convert_object_to_array($result);
                        if (is_array($result))
                            $value = helper_array_get($result, mb_substr($column, $dot_position + 1,  mb_strlen($column)));
                        elseif (is_string($result))
                            $value = $result;
                        else {
                            try {
                                $value = (string) $result;
                            } catch (\Throwable $exception) {
                                $value = 'Невозможно привести к строке';
                            }
                        }
                    } else {
                        $value = $item->$column ?? '';
                        if ($value instanceof \Carbon\Carbon) {$value = $value->format("d.m.Y H:i:s");}
                        if (is_array($value) || is_object($value)) $value = json_encode($value, JSON_THROW_ON_ERROR);
                    }

                    if (is_bool($value)) {
                        if ($value) {
                            $value = '+';
                        } else {
                            $value = '-';
                        }
                    }
                @endphp
                @if(in_array($value, ['green', 'yellow', 'red']))
                    @switch($value)
                        @case('green')
                            <td style="background-color: #59c326;"></td>
                            @break
                        @case('yellow')
                            <td style="background-color: #e9ae3c;"></td>
                            @break
                        @case('red')
                            <td style="background-color: #d32c2c;"></td>
                            @break
                        @default
                            <td>{{ $value }}</td>
                            @break
                    @endswitch
                @else
                    <td>{{ $value }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
</html>
