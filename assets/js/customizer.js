jQuery(document).ready(function($) {
  let placedItems = [];
  let history = [];
  let historyIndex = -1;
  let equipmentList = [];

  function saveToHistory() {
      try {
          history = history.slice(0, historyIndex + 1);
          history.push(JSON.parse(JSON.stringify(placedItems)));
          historyIndex++;
      } catch (error) {
          console.error("Error saving to history:", error);
          alert("An error occurred while saving your design. Please try again.");
      }
  }

  // Implement the checkOverlap function here
  function checkOverlap($item) {
      const itemPosition = $item.position();
      const itemWidth = $item.width();
      const itemHeight = $item.height();

      for (let placedItem of placedItems) {
          if (itemPosition.left < placedItem.left + itemWidth && 
              itemPosition.left + itemWidth > placedItem.left &&
              itemPosition.top < placedItem.top + itemHeight && 
              itemPosition.top + itemHeight > placedItem.top) {
              return true; // There's an overlap
          }
      }
      return false; // No overlap found
  }

  let totalCost = 0;
  const taxRate = 0.1;

$(".equipment-item").hover(function() {
  const name = $(this).data('name');
  const price = $(this).data('price');
  const description = $(this).data('description');
  
  // Get the real-world dimensions from the data attributes
  const realWidth = $(this).data('width');
  const realHeight = $(this).data('height');

  $("#equipment-preview").html(`
      <div class="font-bold">${name}</div>
      <div>Dimensions: ${realWidth}m x ${realHeight}m</div> <!-- Display the real-world dimensions -->
      <div>Price: $${price.toFixed(2)}</div>
      <div>${description}</div>
  `);

  const position = $(this).position();
  $("#equipment-preview").css({
      top: position.top + $(this).height(),
      left: position.left
  }).removeClass('hidden');
}, function() {
  $("#equipment-preview").addClass('hidden');
});


  let rotationDegree = 0;

$(document).on('click', '.rotate-button', function() {
  const $item = $(this).closest('.equipment-item');
  
  // Get the current rotation degree for this specific item or default to 0
  let currentRotation = parseInt($item.data('rotation')) || 0;
  
  // Increment the rotation degree by 90
  let newRotation = (currentRotation + 90) % 360;

  // Apply the new rotation
  $item.css('transform', 'rotate(' + newRotation + 'deg)');
  
  // Check for overlaps
  let isOverlapping = checkOverlap($item);
  
  if (isOverlapping) {
      // If overlapping, revert the rotation and notify the user
      $item.css('transform', 'rotate(' + currentRotation + 'deg)');
      alert("The rotated item overlaps with another item. Please adjust its position.");
  } else {
      // If not overlapping, update the rotation data for this item
      $item.data('rotation', newRotation);
  }
});


  function updateTotalCostDisplay() {
      const totalWithTax = totalCost + (totalCost * taxRate);
      $("#total-cost-display").text(`Total Cost: $${totalWithTax.toFixed(2)}`);
  }

  $(document).on('click', '.equipment-item.placed', function() {
      $(this).remove();
      const itemId = $(this).data('id');
      placedItems = placedItems.filter(item => item.id !== itemId);
      saveToHistory();
      const itemCost = parseFloat($(this).data('cost'));
      totalCost -= itemCost;
      updateTotalCostDisplay();
  });

  $(".save-design-button").on("click", function() {
      $.ajax({
          url: '/path/to/save-endpoint',
          method: 'POST',
          data: {
              design: JSON.stringify(placedItems)
          },
          success: function(response) {
              alert("Design saved successfully!");
          },
          error: function() {
              alert("Error saving design.");
          }
      });
  });

  $(".load-design-button").on("click", function() {
      $.ajax({
          url: '/path/to/load-endpoint',
          method: 'GET',
          success: function(response) {
              try {
                  const savedDesign = JSON.parse(response.design);
                  $("#ftc_customizer").empty();
                  placedItems = [];
                  savedDesign.forEach(item => {
                      const itemClone = $(`.equipment-item[data-id="${item.id}"]`).clone().addClass('placed');
                      itemClone.css({
                          top: item.top,
                          left: item.left
                      });
                      $("#ftc_customizer").append(itemClone);
                      placedItems.push(item);
                  });
                  makePlacedItemsDraggable();
                  saveToHistory();
              } catch (error) {
                  console.error("Error parsing loaded design:", error);
                  alert("An error occurred while loading your design. Please try again.");
              }
          },
          error: function(jqXHR, textStatus, errorThrown) {
              console.error("Error loading design:", errorThrown);
              if (jqXHR.status === 404) {
                  alert("Design not found. Please try again.");
              } else {
                  alert("An error occurred while loading your design. Please try again.");
              }
          }
      });
  });

  const $panzoom = $("#ftc_customizer").panzoom({
      $zoomIn: $(".zoom-in-button"),
      $zoomOut: $(".zoom-out-button"),
      $zoomRange: $(".zoom-range"),
      $reset: $(".reset-button"),
      startTransform: 'scale(1)',
      increment: 0.1,
      minScale: 1,
      maxScale: 3,
      contain: 'automatic'
  });

  $("#ftc_customizer").on("drop", function() {
      $panzoom.panzoom("reset");
  });

  $(".equipment-item").draggable({
      revert: "invalid",
      helper: "clone"
  });

  function makePlacedItemsDraggable() {
      $('.equipment-item.placed').draggable({
          containment: "#ftc_customizer",
          stop: function(event, ui) {
              const itemId = $(ui.helper).data('id');
              const topPosition = ui.position.top;
              const leftPosition = ui.position.left;
              for (let item of placedItems) {
                  if (item.id === itemId) {
                      item.top = topPosition;
                      item.left = leftPosition;
                      break;
                  }
              }
              saveToHistory();
          }
      });
  }

$("#ftc_customizer").droppable({
  accept: ".equipment-item",
  drop: function(event, ui) {
      const itemId = $(ui.draggable).data('id');
      const itemName = $(ui.draggable).data('name');
      const itemPrice = parseFloat($(ui.draggable).data('price'));
      
      // Scale the equipment based on its real-world dimensions
      const realWidth = $(ui.draggable).data('real-width');
      const realHeight = $(ui.draggable).data('real-height');
      const SCALE_RATIO = 10; // This is just an example. Adjust as needed.
      const scaledWidth = realWidth * SCALE_RATIO;
      const scaledHeight = realHeight * SCALE_RATIO;

      const itemClone = $(ui.draggable).clone().css({
          width: scaledWidth,
          height: scaledHeight
      }).addClass("absolute bg-gray-200 p-2 cursor-pointer");
      
      let topPosition = ui.position.top;
      let leftPosition = ui.position.left;

      // Check if it's close to the back zone
      const backZone = $("#back_zone");
      if (leftPosition > backZone.attr("x") && leftPosition < backZone.attr("x") + backZone.attr("width") &&
          topPosition > backZone.attr("y") && topPosition < backZone.attr("y") + backZone.attr("height")) {
          
          // Snap to the back zone
          topPosition = parseFloat(backZone.attr("y"));
          leftPosition = parseFloat(backZone.attr("x"));

          // Check equipment orientation
          if ($(ui.draggable).data("orientation") !== "back-facing") {
              alert("This equipment cannot face this way at the back of the truck/trailer.");
              // Revert the drag
              $(ui.draggable).animate({
                  top: "0",
                  left: "0"
              });
              return;
          }
              // Snap to the back zone
  topPosition = parseFloat(backZone.attr("y"));
  leftPosition = parseFloat(backZone.attr("x"));
      }

      itemClone.css({
          top: topPosition,
          left: leftPosition
      });

      // Check for overlaps
      let isOverlapping = false;
      placedItems.forEach(item => {
          if (leftPosition < item.left + scaledWidth && leftPosition + scaledWidth > item.left &&
              topPosition < item.top + scaledHeight && topPosition + scaledHeight > item.top) {
              isOverlapping = true;
          }
      });

      if (!isOverlapping) {
          const customizerWidth = $("#ftc_customizer").width();
          const customizerHeight = $("#ftc_customizer").height();
          if (leftPosition + scaledWidth > customizerWidth || topPosition + scaledHeight > customizerHeight) {
              $(ui.draggable).animate({
                  top: "0",
                  left: "0"
              });
              return;
          }

          const itemCost = parseFloat($(ui.draggable).data('cost'));
          totalCost += itemCost;
          updateTotalCostDisplay();

          itemClone.addClass('placed');
          $(this).append(itemClone);
          placedItems.push({
              id: itemId,
              top: topPosition,
              left: leftPosition
          });

          // Add the item to the equipment list
          equipmentList.push({
              id: itemId,
              name: itemName,
              quantity: 1, // You can adjust the quantity if needed
              price: itemPrice
          });

          // Update the itemized list
          updateItemizedList();


          // Make the placed item draggable within the customizer
          makePlacedItemsDraggable();

          // Save the current state after placing the item
          saveToHistory();

      } else {
          // If overlapping, revert the item back
          $(ui.draggable).animate({
              top: "0",
              left: "0"
          });
      }
  }
});

// Function to update the itemized list
function updateItemizedList() {
  // Clear the existing list
  $("#itemized-list").empty();

  // Append each equipment item to the list
  equipmentList.forEach(item => {
      const itemRow = `<div>${item.name} - ${item.price.toFixed(2)}</div>`;
      $("#itemized-list").append(itemRow);
  });
}


  // Event listener for the "Undo" button
  $(".undo-button").on("click", function() {
      if (historyIndex > 0) {
          historyIndex--;
          placedItems = history[historyIndex];
          renderItems();
      }
  });

  // Event listener for the "Redo" button
  $(".redo-button").on("click", function() {
      if (historyIndex < history.length - 1) {
          historyIndex++;
          placedItems = history[historyIndex];
          renderItems();
      }
  });

  function renderItems() {
      $("#ftc_customizer").empty();
      placedItems.forEach(item => {
          const itemClone = $(`.equipment-item[data-id="${item.id}"]`).clone().addClass('placed');
          itemClone.css({
              top: item.top,
              left: item.left
          });
          $("#ftc_customizer").append(itemClone);
      });
  }
});

