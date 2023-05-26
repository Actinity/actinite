<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Admin</title>
    <link rel="stylesheet" href="{{ actinite_mix('/app.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/solid.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/regular.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/fontawesome.css" crossorigin="anonymous">

</head>
<body>
<div id="actinite">
    <actinite
        :user="{{ json_encode(auth()->user()) }}"
        asset-root="{{ config('app.asset_url') }}"
        max-upload-size="{{ \Actinity\Actinite\Services\AssetService::getMaxUploadSize() }}"
        :roots="{{ json_encode(\Actinity\Actinite\Services\TreeService::rootIds()) }}"
        :features="{{ json_encode(config('actinite.features')) }}"
    ></actinite>

    <!--<mailtrapper></mailtrapper>-->
</div>
<script src="{{ actinite_mix('/app.js') }}"></script>
</body>
</html>
