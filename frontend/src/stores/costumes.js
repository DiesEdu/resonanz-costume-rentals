import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCostumesStore = defineStore('costumes', () => {
  const costumes = ref([
    {
      id: 1,
      name: 'Elegant Victorian Ball Gown',
      category: 'Historical',
      price: 85,
      size: ['S', 'M', 'L'],
      description:
        'Stunning 19th-century inspired ball gown with intricate lace details and corset bodice. Perfect for themed parties and historical reenactments.',
      image:
        'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.8,
      reviews: 24,
    },
    {
      id: 2,
      name: 'Superhero Spider Suit',
      category: 'Superhero',
      price: 65,
      size: ['S', 'M', 'L', 'XL'],
      description:
        'High-quality Spider-Man inspired suit with muscle padding and web patterns. Includes mask and wrist web-shooters.',
      image:
        'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.9,
      reviews: 56,
    },
    {
      id: 3,
      name: 'Renaissance Pirate Captain',
      category: 'Pirate',
      price: 75,
      size: ['M', 'L', 'XL'],
      description:
        'Authentic pirate captain outfit with tricorn hat, leather boots, and detailed coat. Includes prop sword and belt accessories.',
      image:
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.7,
      reviews: 18,
    },
    {
      id: 4,
      name: 'Princess Elsa Ice Dress',
      category: 'Disney',
      price: 55,
      size: ['XS', 'S', 'M'],
      description:
        "Magical ice queen dress with shimmering fabric and flowing cape. Perfect for children's parties and cosplay events.",
      image:
        'https://images.unsplash.com/photo-1534445967719-8ae7b972b1a5?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.9,
      reviews: 89,
    },
    {
      id: 5,
      name: '1920s Gatsby Flapper Dress',
      category: 'Vintage',
      price: 70,
      size: ['XS', 'S', 'M', 'L'],
      description:
        'Art Deco inspired flapper dress with beaded fringe and headband. Perfect for Great Gatsby themed parties.',
      image:
        'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.6,
      reviews: 32,
    },
    {
      id: 6,
      name: 'Medieval Knight Armor',
      category: 'Historical',
      price: 95,
      size: ['M', 'L', 'XL'],
      description:
        'Full metal-effect knight armor with chainmail details. Includes helmet, gauntlets, and sword. Great for medieval fairs.',
      image:
        'https://images.unsplash.com/photo-1598556883438-2c96b1b5b5b5?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.8,
      reviews: 15,
    },
    {
      id: 7,
      name: 'Wonder Woman Costume',
      category: 'Superhero',
      price: 70,
      size: ['XS', 'S', 'M', 'L'],
      description:
        'Iconic Amazon warrior costume with tiara, arm bands, and lasso accessory. High-quality materials for comfort.',
      image:
        'https://images.unsplash.com/photo-1569003339405-ea396a5a8a90?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.7,
      reviews: 43,
    },
    {
      id: 8,
      name: 'Harry Potter Wizard Robes',
      category: 'Fantasy',
      price: 45,
      size: ['S', 'M', 'L'],
      description:
        'Authentic Hogwarts house robes with house crest. Choose your house! Includes wand and scarf.',
      image:
        'https://images.unsplash.com/photo-1547592166-23acbe346499?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.9,
      reviews: 67,
    },
    {
      id: 9,
      name: 'Vampire Count Dracula',
      category: 'Horror',
      price: 60,
      size: ['M', 'L', 'XL'],
      description:
        'Classic vampire costume with velvet cape, vest, and fangs. Perfect for Halloween and gothic events.',
      image:
        'https://images.unsplash.com/photo-1509557965875-b88c97052f0e?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.5,
      reviews: 28,
    },
    {
      id: 10,
      name: 'Astronaut Space Suit',
      category: 'Career',
      price: 80,
      size: ['S', 'M', 'L'],
      description:
        'NASA-inspired astronaut suit with patches, helmet, and backpack. Great for space-themed parties.',
      image:
        'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.8,
      reviews: 21,
    },
    {
      id: 11,
      name: 'Geisha Traditional Kimono',
      category: 'Cultural',
      price: 90,
      size: ['XS', 'S', 'M'],
      description:
        'Authentic Japanese kimono with obi belt, traditional footwear, and hair accessories. Beautiful silk fabric.',
      image:
        'https://images.unsplash.com/photo-1492571350019-22de08371fd3?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.9,
      reviews: 34,
    },
    {
      id: 12,
      name: 'Zombie Apocalypse Survivor',
      category: 'Horror',
      price: 50,
      size: ['S', 'M', 'L', 'XL'],
      description:
        'Post-apocalyptic survivor outfit with distressed clothing, fake blood, and survival gear props.',
      image:
        'https://images.unsplash.com/photo-1509248961158-e54f6934749c?w=500&auto=format&fit=crop&q=60',
      available: true,
      rating: 4.4,
      reviews: 19,
    },
  ])

  const categories = computed(() => {
    const cats = new Set(costumes.value.map((c) => c.category))
    return ['All', ...Array.from(cats)]
  })

  const getCostumeById = (id) => {
    return costumes.value.find((c) => c.id === Number(id))
  }

  const getCostumesByCategory = (category) => {
    if (category === 'All') return costumes.value
    return costumes.value.filter((c) => c.category === category)
  }

  const searchCostumes = (query) => {
    const lowerQuery = query.toLowerCase()
    return costumes.value.filter(
      (c) =>
        c.name.toLowerCase().includes(lowerQuery) ||
        c.category.toLowerCase().includes(lowerQuery) ||
        c.description.toLowerCase().includes(lowerQuery),
    )
  }

  return {
    costumes,
    categories,
    getCostumeById,
    getCostumesByCategory,
    searchCostumes,
  }
})
