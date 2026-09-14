<?php header('Content-Type:text/plain'); $lua = str_replace('www.', '', $_SERVER["SERVER_NAME"]); $lua = strtolower($lua); $lua = str_replace(['.', '/early08'], ['%.' ], $lua); $site = 'localhost';
// Early08 adaptation of the zyphie FixAssetLinks render helper.
// Rewrites classic roblox.com asset URLs inside a loaded place to this site's asset handler.
$domain = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$escaped = preg_quote($domain, '/');
$lua = str_replace('.', '%.', $domain);
?>
--Early08 FixAssetLinks (ported from zyphie)
local assetPropertyNames = {"Texture", "TextureId", "SoundId", "MeshId", "SkyboxUp", "SkyboxLf", "SkyboxBk", "SkyboxRt", "SkyboxFt", "SkyboxDn", "PantsTemplate", "ShirtTemplate", "Graphic", "Image", "LinkedSource", "AnimationId"}
local variations = {"http://www%.roblox%.com/asset/%?id=", "http://www%.roblox%.com/asset%?id=", "http://roblox%.com/asset/%?id=", "http://roblox%.com/asset%?id=", "http://%.roblox%.com/asset/%?id=", "http://%.roblox%.com/asset%?id=", "http://%nounblx%.cf/asset%?id=", "http://%xdiscuss%.net/asset%?id=", "http://%api%.xdiscuss%.net/asset%?id=", "http://%ogblox%.xyz/asset%?id=", "http://%ogblox%.net/asset%?id="}
local converturl = "http://<?php echo $escaped ?>/early08/asset/?id="

function GetDescendants(o)
    local allObjects = {}
    function FindChildren(Object)
       for _,v in pairs(Object:GetChildren()) do
            table.insert(allObjects,v)
            FindChildren(v)
        end
    end
    FindChildren(o)
    return allObjects
end

local replacedProperties = 0

for i, v in pairs(GetDescendants(game)) do
  for _, property in pairs(assetPropertyNames) do
    pcall(function()
      if v[property] and not v:FindFirstChild(property) then
        assetText = string.lower(v[property])
        for _, variation in pairs(variations) do
          v[property], matches = string.gsub(assetText, variation, converturl)
          if matches > 0 then
            replacedProperties = replacedProperties + 1
            print("Replaced " .. property .. " asset link for " .. v.Name)
            break
          end
        end
      end
    end)
  end
end

print("DONE! Replaced " .. replacedProperties .. " properties")