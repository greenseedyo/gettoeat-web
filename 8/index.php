<!DOCTYPE html>
<html>
  <head>
    <title>Sentiment Echo</title>
    <meta charset="utf-8">
    <script>
var ethhash = ethhash;
if (undefined !== ethhash) {
	window._albaInject = { tokenId: 1, seed: ethhash };
}

// Version 0.0.4
var albaGlobalState = {
  isComplete: false,
  metadata: null,
};
const albaInjected = window._albaInject || {};
function albaCoerceToStrings(obj) {
  const coercedObj = {};
  for (const [key, value] of Object.entries(obj)) {
    coercedObj[key] = String(value);
  }
  return coercedObj;
}
function albaSfc32(a, b, c, d) {
  return function () {
    a >>>= 0;
    b >>>= 0;
    c >>>= 0;
    d >>>= 0;
    var t = (a + b) | 0;
    a = b ^ (b >>> 9);
    b = (c + (c << 3)) | 0;
    c = (c << 21) | (c >>> 11);
    d = (d + 1) | 0;
    t = (t + d) | 0;
    c = (c + t) | 0;
    return (t >>> 0) / 4294967296;
  };
}
window.alba = {
  isComplete: () => {
    return albaGlobalState.isComplete;
  },
  setComplete: (done) => {
    albaGlobalState.isComplete = Boolean(done);
  },
  getMetadata: () => {
    return albaGlobalState.metadata;
  },
  setMetadata: (values) => {
    albaGlobalState.metadata = albaCoerceToStrings(values);
  },
  prng: (seed) => {
    const [_, seedHex] = seed.split("x");
    return albaSfc32(
      parseInt(seedHex.slice(0, 8), 16),
      parseInt(seedHex.slice(8, 16), 16),
      parseInt(seedHex.slice(16, 24), 16),
      parseInt(seedHex.slice(24, 32), 16)
    );
  },
  _testSeed: () => {
    const buf = new Uint32Array(8);
    window.crypto.getRandomValues(buf);
    const newSeed = Array.from(buf, (n) => n.toString(16))
      .join("")
      .padEnd(64, "0");
    return `0x${newSeed}`;
  },
  params: {
    tokenId: albaInjected.tokenId,
    seed: albaInjected.seed,
    res: albaInjected.res,
    width: albaInjected.res,
    isRenderer: albaInjected.isRenderer,
  },
};

    </script>

    <link rel="stylesheet" href="./style.css">

    <!-- if you need to import js scripts do it here -->
    <script src="./p5.min.js"></script>
    <script src="./all.min.js?v=<?= time() ?>"></script>
  </head>
  <body>
    <main>
    </main>
  </body>
</html>
