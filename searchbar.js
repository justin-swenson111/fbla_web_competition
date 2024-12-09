
const itemCardTemplate = document.querySelector("[data-item-template]")
const itemCardContainer = document.querySelector("[data-item-cards-container]")
const searchInput = document.querySelector("[data-search]")

let items = []

searchInput.addEventListener("input", e => {
  const value = e.target.value.toLowerCase()
  items.forEach(item => {
    const isVisible =
    value && (item.item.toLowerCase().includes(value) ||
    item.hyperlink.toLowerCase().includes(value)) 
      item.element.classList.toggle("hide", !isVisible)
  })
})

fetch("/search.JSON")
  .then(res => res.json())
  .then(data => {
    items = data.map(item => {
      const card = itemCardTemplate.content.cloneNode(true).children[0]
      const header = card.querySelector("[data-header]")
      const link = card.querySelector("[data-link]")
      header.textContent = item.item
      link.href = item.hyperlink
       
      
      card.classList.add("hide")
      
      itemCardContainer.append(card)
      return { item: item.item, hyperlink: item.hyperlink,element: card }
    })
  })
