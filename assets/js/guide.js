document.addEventListener('DOMContentLoaded', () => {
  const skinTypeEl = document.getElementById('skinType');
  const goalEl = document.getElementById('goal');

  const stepCleanse = document.querySelector('[data-step="cleanse"]');
  const stepActive = document.querySelector('[data-step="active"]');
  const stepCream = document.querySelector('[data-step="cream"]');
  const stepSpf = document.querySelector('[data-step="spf"]');

  if (!skinTypeEl || !goalEl || !stepCleanse || !stepActive || !stepCream || !stepSpf) return;

  const cleanseBySkin = {
    normal: 'Мягкий гель или пенка, без “скрипа”. Утром и вечером.',
    dry: 'Крем‑гель или мягкое молочко. Минимум ПАВ, без пересушивания.',
    oily: 'Гель/пенка, которые хорошо смывают SPF и себум, но без жёстких сульфатов.',
    combo: 'Гель средней мягкости: баланс для Т‑зоны и щёк.',
    sensitive: 'Самое мягкое очищение без отдушек, спирта и агрессивных кислот.'
  };

  const creamBySkin = {
    normal: 'Лёгкий или средний крем, который комфортно закрывает уход без липкости.',
    dry: 'Питательный крем с церамидами/скваланом. При необходимости — слой плотнее на ночь.',
    oily: 'Лёгкий крем‑гель, некомедогенные текстуры, без тяжёлых масел.',
    combo: 'Лёгкий крем на Т‑зону и более комфортный на сухие участки по потребности.',
    sensitive: 'Крем для барьера: церамиды, пантенол, минимальная парфюмерия.'
  };

  const activeByGoal = {
    hydration: 'Увлажняющий актив: гиалуроновая кислота, бетаин, пантенол. Наносите на слегка влажную кожу.',
    acne: 'Против высыпаний: BHA (салициловая) 2–4 раза в неделю или ниацинамид ежедневно. Начинайте постепенно.',
    tone: 'Для тона и сияния: витамин C утром или мягкие AHA 1–3 раза в неделю.',
    antiage: 'Анти‑эйдж: ретиноиды вечером 2–3 раза в неделю с постепенным увеличением.',
    barrier: 'Барьер: церамиды, пантенол, центелла. Минимизируйте кислоты и ретиноиды на время восстановления.'
  };

  const spfByGoal = {
    hydration: 'SPF ежедневно: комфортная текстура, обновляйте при длительном пребывании на улице.',
    acne: 'SPF ежедневно: выбирайте некомедогенные формулы (флюиды/гели).',
    tone: 'SPF ежедневно: особенно важно при витамине C и кислотах.',
    antiage: 'SPF ежедневно: это ключ к профилактике фотостарения.',
    barrier: 'SPF ежедневно: мягкие формулы без жжения и отдушек.'
  };

  function update() {
    const skin = skinTypeEl.value || 'normal';
    const goal = goalEl.value || 'hydration';

    stepCleanse.textContent = cleanseBySkin[skin] || cleanseBySkin.normal;
    stepActive.textContent = activeByGoal[goal] || activeByGoal.hydration;
    stepCream.textContent = creamBySkin[skin] || creamBySkin.normal;
    stepSpf.textContent = spfByGoal[goal] || spfByGoal.hydration;
  }

  skinTypeEl.addEventListener('change', update);
  goalEl.addEventListener('change', update);
  update();
});

